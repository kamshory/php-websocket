# Changelog - Version 2.0.0

This version marks a major overhaul of the PHP WebSocket Chat application, focusing on new features, modern development practices, and a significantly improved user experience.

## ✨ New Features

-   **Chatroom Functionality**: Users can now specify a chatroom upon login. The server logic has been updated to ensure that messages are isolated and only broadcast to clients within the same room.
-   **Automatic Reconnection**: The client-side JavaScript will now automatically attempt to reconnect to the server every 3 seconds if the connection is lost, providing a more resilient user experience.

## 🚀 Changes & Improvements

-   **UI/UX Overhaul**:
    -   The entire user interface for both the login and chat pages has been redesigned using Bootstrap 4 for a clean, modern, and responsive layout.
    -   Standard browser `alert()` popups have been replaced with more elegant Bootstrap modals.
    -   A "Logout" button has been added to the chat page.

-   **PHP Code Refactoring**:
    -   All PHP classes (`ChatServer`, `ChatClient`, `Utility`) now have comprehensive PHPDoc blocks with explicit type hints and class descriptions.
    -   Local variable naming has been standardized to `camelCase` for better code consistency.
    -   Deprecated functions like `ereg` and the insecure `/e` modifier in `preg_replace` have been replaced with modern, secure alternatives (`preg_match` and `preg_replace_callback`).

-   **JavaScript Refactoring**:
    -   The dependency on jQuery has been completely removed. The entire client-side script now uses modern, vanilla JavaScript (ES6+).
    -   String concatenation has been replaced with template literals for improved readability.
    -   DOM manipulation now uses the more performant `appendChild` method instead of `innerHTML +=`.

## 🐞 Bug Fixes

-   **Socket Binding**: Fixed an issue where the server could not be restarted immediately by setting the `SO_REUSEADDR` socket option, allowing the port to be reused without a waiting period.
