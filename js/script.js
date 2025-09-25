document.addEventListener("DOMContentLoaded", function() {
	//create a new WebSocket object.
	var wsUri = "ws://localhost:8889/chat/server.php";
	var websocket = new WebSocket(wsUri);

	// Get DOM elements
	const sendBtn = document.getElementById('send-btn');
	const messageBox = document.getElementById('message_box');
	const messageInput = document.getElementById('message');

	sendBtn.addEventListener('click', function() { //use clicks message send button
		send();
	});

	document.addEventListener('keyup', function(e) {
		if (e.key === 'Enter') { // Use 'key' property which is more modern than keyCode
			send();
		}
	});

	//#### Message received from server?
	websocket.onmessage = function(ev) {
		var msg = JSON.parse(ev.data); //PHP sends Json data
		var type = msg.type; //message type
		var umsg = msg.message; //message text
		var uname = msg.name; //user name
		
		if(type == 'usermsg')
		{
			messageBox.innerHTML += "<div><span class=\"user_name\">"+uname+"</span> : <span class=\"user_message\">"+umsg+"</span></div>";
		}
		if(type == 'system')
		{
			messageBox.innerHTML += "<div class=\"system_msg\">"+umsg+"</div>";
		}
		
		messageInput.value = ''; //reset text
	};

	function send()
	{
		var mymessage = messageInput.value.trim(); //get message text and trim whitespace
		
		if(mymessage == ""){ //emtpy message?
			alert("Please enter a message.");
			return;
		}
		
		//prepare json data
		var msg = {
			message: mymessage,
			name: document.querySelector('meta[name="username"]').getAttribute('content')
		};
		//convert and send data to server
		websocket.send(JSON.stringify(msg));
	}

	websocket.onerror	= function(ev){ messageBox.innerHTML += "<div class=\"system_error\">Error - "+ev.data+"</div>"; };
	websocket.onclose 	= function(ev){ messageBox.innerHTML += "<div class=\"system_msg\">Connection closed</div>"; };
});