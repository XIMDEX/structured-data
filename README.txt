WEB CLIENT FOR SCHEMA.ORG EXPLORER
1. Introduction
This document corresponds to the frontend (web client) of the Schema.org Explorer application. It is a light, self-contained web client built exclusively using standard HTML, CSS, and JavaScript (referred to as "Vanilla JS").

Its primary function is to serve as the user interface for interacting with the main project's API, enabling the visualization and management (CRUD operations) of data types based on the Schema.org standard.

2. Employed Technologies
The project is designed to be lightweight and has no third-party framework dependencies.

HTML5: Application structure.

CSS3: General styling and responsive design. It includes specific style definitions for CRUD operations (.read-btn, .update-btn, etc.).

JavaScript (Vanilla JS): Client-side logic, DOM event handling, and asynchronous communication (fetch) with the API.

3. Configuration and Execution
The application is client-server in nature. It requires the backend API to be operational for full functionality.

3.1 Prerequisites
The sole functional requirement is that the Backend API Service is running and accessible.

3.2 API URL (Configuration)
The base URL of the API is defined in the app.js file. It is crucial to check and modify this constant if the backend environment differs from the local setup:

JavaScript

const API_BASE_URL = 'http://localhost/structured-data-definitivo/public/api/v1';
3.3 Installation Steps
Obtain Files: Download or clone the files (index.html, styles.css, app.js).

Serve Locally: Open index.html directly in the browser or, preferably, serve the files using a local web server (e.g., Apache, Nginx, or the VS Code Live Server extension) to ensure proper route handling and prevent CORS issues.

4. Interface Functionality
Once loaded, the interface allows for the following interactions:

Schema Loading: The user can request a schema definition by entering its numerical ID or name (e.g., "Person") into the main input field.

Quick Search: The header includes a secondary search field to quickly find and load schemas.

CRUD Interaction: The interface is designed to display and manage the data associated with a loaded schema, featuring specific buttons and styles for Read, Update, and other related CRUD operations.