# Tbot Game Interface

A web-based tool designed to interact with game APIs hosted on `tbot.xyz`. This interface allows users to submit scores and retrieve high score lists for various games on the platform by providing the game URL and score. The tool automatically parses the necessary game name and session data from the provided URL.

## ✨ Features

* **Score Submission:** Allows users to submit scores to supported `tbot.xyz` games.
* **High Score Retrieval:** Fetches and displays high score lists (though this functionality is primarily in the PHP service class and not exposed via a separate button in the current UI).
* **Automatic Data Extraction:** Intelligently parses the game name and required `curData` (session/user information) from the full game URL.
* **Modern User Interface:** Features a responsive, dark-themed UI with dynamic elements like a mouse trail effect.
* **Configurable Logging:** Option to display the full server response for debugging or detailed information.
* **Flexible Service Class:** The underlying `TbotGameService.php` is designed to be adaptable for different games on the `tbot.xyz` platform that share a similar API pattern.

## 🛠️ Setup Instructions

### Prerequisites

* PHP 8.0 or higher (due to the use of union types and modern features).
* cURL extension for PHP enabled.
* Composer (for managing PHP dependencies, primarily PHPUnit for testing).
* A web server (like Apache or Nginx) configured to serve PHP files. The document root should point to the `public/` directory of this project.

### Installation

1.  **Clone the repository:**
    ```bash
    git clone [https://github.com/imnotjavad/GameBotHacks.git](https://github.com/imnotjavad/GameBotHacks.git)
    cd GameBotHacks 
    ```
    *(Replace `https://github.com/imnotjavad/GameBotHacks.git` with your actual repository URL if different)*

2.  **Install PHP dependencies:**
    ```bash
    composer install
    ```
    This will install PHPUnit, which is listed in your `composer.json` under `require-dev`.

3.  **Web Server Configuration:**
    * Configure your web server (e.g., Apache, Nginx) so that its document root points to the `public/` directory of this project.
    * Ensure your web server is set up to process PHP files.

4.  **(Optional) Environment Variables:**
    * Currently, API endpoint details are hardcoded in `src/Service/TbotGameService.php`. For more advanced setups or if you plan to make these configurable, you could implement `.env` file support. If so, you would:
        * Copy `.env.example` (you'll need to create this file) to `.env`.
        * Fill in the necessary environment variables in `.env`.
        * Ensure `vlucas/phpdotenv` is added to your `composer.json` if you choose this path.

## 🚀 Usage

1.  Access the `index.php` page through your web browser (e.g., `http://localhost/index.php` or your configured domain).
2.  **Full Game URL:** Enter the complete URL of the `tbot.xyz` game you want to interact with. This URL must include the hash fragment (`#`) containing the game and session data.
    * *Example:* `https://tbot.xyz/math/#eyJ1Ijo1ODAzNTEwMDE4LCJuIjoiT.....`
3.  **Score:** Enter the numerical score you wish to submit.
4.  **Show Full Server Response Log (Optional):** Check this box if you want to see the complete, raw response from the server after submission.
5.  Click the "Submit Score" button.
6.  The result of the submission (success or error message, and optionally the server response) will be displayed on the page.
7.  Click the "Clear Form" button to reset the input fields and any displayed messages.

## 🧪 How to Run Tests

This project uses PHPUnit for unit testing. The primary tests are for the `TbotGameService` class.

1.  Ensure you have run `composer install` to install PHPUnit.
2.  From the project root directory, run:
    ```bash
    composer test
    ```
    Alternatively, you can run PHPUnit directly:
    ```bash
    ./vendor/bin/phpunit
    ```
    *Note: The current `TbotGameServiceTest.php` provides a basic test for the constructor. More comprehensive tests, especially for methods making cURL requests (which would require mocking), should be added for better coverage.*

## ⚠️ Important Notes & Disclaimer

* This tool interacts with API endpoints of `tbot.xyz` that have been identified through client-side analysis of the games. These are likely **unofficial endpoints**.
* The functionality of this tool is dependent on the current state of the `tbot.xyz` API. If the API changes, this tool may cease to function correctly or at all.
* **Use this tool responsibly and at your own risk.** Always respect the terms of service of `tbot.xyz` and any games hosted on it.
* This project is provided for educational and experimental purposes. The developers/contributors are not responsible for any misuse or any consequences arising from the use of this tool.
* The cURL options `CURLOPT_SSL_VERIFYPEER` and `CURLOPT_SSL_VERIFYHOST` are currently set to `false` in `TbotGameService.php`. **This is a security risk and should ONLY be used for local development and debugging if you are experiencing SSL certificate issues. For any production or public-facing deployment, these options MUST be set to `true` or removed (as `true` is the default for `CURLOPT_SSL_VERIFYPEER`).**

## 📜 License

This project is licensed under the **MIT License**. Please see the `LICENSE` file for more details.
(You should create a `LICENSE` file in your repository and paste the text of the MIT License into it.)

## 🙌 Contributing (Optional)

If you'd like to contribute to this project, please feel free to fork the repository, make your changes, and submit a pull request. For major changes, please open an issue first to discuss what you would like to change.