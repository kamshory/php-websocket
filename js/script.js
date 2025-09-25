document.addEventListener("DOMContentLoaded", function() {
	// Get DOM elements
	const sendBtn = document.getElementById('send-btn');
	const messageBox = document.getElementById('message_box');
	const messageInput = document.getElementById('message');
	const wsUri = document.querySelector('meta[name="websocket"]').getAttribute('content');
	const username = document.querySelector('meta[name="username"]').getAttribute('content');
	const chatroom = document.querySelector('meta[name="chatroom"]').getAttribute('content');
	let websocket;

	function connect() {
		websocket = new WebSocket(wsUri);

		websocket.onopen = function(ev) {
			displayMessage(`<div class="system_msg">Connected to the server.</div>`);
		}

		websocket.onmessage = function(ev) {
			const msg = JSON.parse(ev.data); //PHP sends Json data
			const type = msg.type; //message type
			const umsg = msg.message; //message text
			const uname = msg.name; //user name

			if (type == 'usermsg') {
				displayMessage(`<div><span class="user_name">${uname}</span> : <span class="user_message">${umsg}</span></div>`);
			}
			if (type == 'system') {
				displayMessage(`<div class="system_msg">${umsg}</div>`);
			}

			messageInput.value = ''; //reset text
		};

		websocket.onerror = function(ev) {
			displayMessage(`<div class="system_error">Error - Connection to the server failed.</div>`);
		};

		websocket.onclose = function(ev) {
			displayMessage(`<div class="system_msg">Connection closed. Reconnecting in 3 seconds...</div>`);
			// Wait 3 seconds before trying to reconnect
			setTimeout(function() {
				connect();
			}, 3000);
		};
	}

	// Initial connection
	connect();

	sendBtn.addEventListener('click', function() {
		send();
	});

	document.addEventListener('keyup', function(e) {
		// Ensure the user is focused on the message input
		if (e.key === 'Enter' && document.activeElement === messageInput) {
			send();
		}
	});

	function send() {
		const mymessage = messageInput.value.trim();
		if (mymessage == "") {
			showAlertModal("Please enter a message.");
			return;
		}

		// Check if the websocket is connected
		if (websocket.readyState !== WebSocket.OPEN) {
			showAlertModal("Not connected to the server. Please wait.");
			return;
		}

		//prepare json data
		const msg = {
			message: mymessage,
			name: username,
			chatroom: chatroom
		};
		//convert and send data to server
		websocket.send(JSON.stringify(msg));
	}

	function displayMessage(message) {
		// Create a temporary container element to parse the HTML string
		const tempContainer = document.createElement('div');
		tempContainer.innerHTML = message;

		// Get the actual message element from the container
		const messageElement = tempContainer.firstElementChild;

		// Append the new element to the message box to avoid re-parsing the whole container
		if (messageElement) {
			messageBox.appendChild(messageElement);
		}
		messageBox.scrollTop = messageBox.scrollHeight; // Scroll to the bottom
	}

	function showAlertModal(message) {
		document.getElementById('alertModalBody').textContent = message;
		$('#alertModal').modal('show');
	}
});