# Hyperlinear Plugin

## Overview

The **Hyperlinear Plugin** is a custom WordPress plugin developed to streamline the process of adding users to a Brevo (formerly Sendinblue) contact list via a simple REST API endpoint. By using this plugin, website owners can effortlessly collect user information from a WordPress page and add them to their preferred email marketing list managed in Brevo.


## Installation

1. The plugin expects that a Wordpress page with slug `/newsletter-signup` exists. A client-side GET request will be made to this endpoint to trigger the sign-up logic.

2. The sign-up form code (check `pages/newsletter_form.html`)  must be inserted in a Wordpress page as an HTML block, currently configured in `/newsletter-form`. The Brevo contact list ID is currently set to `2`, change this value in the Wordpress page to switch to a different list.

2. Upload the plugin files to the WordPress directory:  
   Place the plugin folder in `/wp-content/plugins/hyperlinear-plugin`.

3. Create a `.env` file in the plugin directory. It must contain the following keys with respective values:
```
BREVO_API_KEY=xxx
BREVO_ENDPOINT=https://api.brevo.com/v3/contacts
```
This repo contains a `.env.local` copy of this file. Rename it to `.env` and fill it out with the correct values.

4. Activate the plugin:
- Go to your WordPress admin dashboard.
- Navigate to **Plugins** > **Installed Plugins**.
- Find the "Hyperlinear Plugin" and click **Activate**.

5. Configure your contact list:
- Use the `list_id` parameter in the API call to specify the Brevo list where users should be added.

## Usage

### Setting Up the Newsletter Signup Page

1. **Create a Page in WordPress**:
- Go to **Pages** > **Add New** in the WordPress admin dashboard.
- Create a page titled "Newsletter Signup".
- Publish the page.

2. **Enable the Endpoint**:
- When the page `/newsletter-signup` is accessed, the plugin's API will automatically handle the requests.

3. **Send User Information**:
- Send a GET request to `/newsletter-signup` with the following parameters:
  - `first_name`: The user's first name (required).
  - `last_name`: The user's last name (required).
  - `email`: The user's email address (required).
  - `list_id`: The Brevo contact list ID where the user should be added (required).
- Example request:  
  ```
  /newsletter-signup?first_name=John&last_name=Doe&email=johndoe@example.com&list_id=12345
  ```

## Error Messages

The plugin provides detailed error messages for various scenarios:

- **400 Bad Request**:
- Missing required parameters (`first_name`, `last_name`, `email`, or `list_id`).
- Example message: "Missing required parameters: first_name, last_name, or email."

- **409 Conflict**:
- The email address is already registered in the Brevo contact list.
- Example message: "Sorry, the provided email is already registered."

- **500 Internal Server Error**:
- Failure to connect to the Brevo API.
- Example message: "Failed to connect to Brevo API."
