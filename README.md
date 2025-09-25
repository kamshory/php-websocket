# PHP WebSocket Chat

This is a simple real-time chat application built using WebSockets with a backend server written entirely in PHP using low-level socket functions. This project serves as a basic example of how the WebSocket protocol can be implemented from scratch in PHP without external libraries like Ratchet.

## Features

- Real-time chat between multiple clients.
- Custom WebSocket server written in pure PHP.
- Clean and responsive user interface using Bootstrap 4.
- Simple login system based on PHP sessions.
- No dependency on external JavaScript libraries (uses Vanilla JS).

## Technologies Used

- **Backend**: PHP 7.x or newer
- **Frontend**: HTML5, Bootstrap 4, JavaScript (ES6)
- **Protocol**: WebSocket (RFC 6455)

## How to Run

1.  **Prerequisites**: Make sure you have PHP installed and accessible from the command line. You also need a web server like Apache (e.g., from XAMPP) to serve the frontend files.

2.  **Place Files**: Place all project files inside your web server's root directory (e.g., `d:\xampp\htdocs\php-websocket`).

3.  **Run the WebSocket Server**: Open a terminal or command prompt, navigate to the project directory, and run the following command:
    ```sh
    php server.php
    ```
    The server will now be running and listening for connections on `ws://localhost:8889`.

4.  **Access the Application**: Open your web browser and navigate to the login page:
    `http://localhost/php-websocket/login.php`

5.  **Login and Start Chatting**: Enter any username and click "Login". You will be redirected to the chat room. Open multiple tabs or browsers to simulate multiple users.

## File Structure

- `server.php`: Script to start the WebSocket server.
- `index.php`: Main chat interface page.
- `login.php`: Login page to set up the user session.
- `classes/ChatServer.php`: Core logic for handling WebSocket connections, handshakes, and message broadcasting.
- `classes/ChatClient.php`: Class representing each connected client.
- `classes/Utility.php`: Collection of helper functions for parsing headers, cookies, and session data.
- `js/script.js`: Client-side JavaScript code for handling the WebSocket connection and manipulating the DOM.
