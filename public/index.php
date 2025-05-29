<?php
declare(strict_types=1);

// Autoloading (if using Composer) or direct require
require_once __DIR__ . '/../src/Service/TbotGameService.php';
// Potentially: require_once __DIR__ . '/../vendor/autoload.php';

// Initialize variables
$alertType = '';
$alertMessage = '';
$responseData = null;
$submittedUrl = '';
$submittedScore = '';
$showFullLogChecked = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullGameUrl = trim($_POST['full_game_url'] ?? '');
    $scoreStr    = trim($_POST['score'] ?? '');
    $showFullLog = isset($_POST['show_log']);

    $submittedUrl = $fullGameUrl;
    $submittedScore = $scoreStr;
    $showFullLogChecked = $showFullLog;

    // --- Input Validation ---
    if (empty($fullGameUrl) || empty($scoreStr)) {
        $alertType = 'error';
        $alertMessage = 'Error: Full Game URL and Score fields are required!';
    } elseif (!filter_var($fullGameUrl, FILTER_VALIDATE_URL)) {
        $alertType = 'error';
        $alertMessage = 'Error: Invalid Full Game URL format!';
    } elseif (!ctype_digit($scoreStr) || (int)$scoreStr < 0) {
        $alertType = 'error';
        $alertMessage = 'Error: Score must be a non-negative numeric value!';
    } else {
        $score = (int)$scoreStr;
        $gameName = null;
        $curData = null;
        
        // --- URL Parsing Logic ---
        // (This could be moved to a helper class in src/Utils/UrlParser.php for more complex scenarios)
        $urlParts = parse_url($fullGameUrl);

        if (isset($urlParts['path'])) {
            $pathSegments = explode('/', trim($urlParts['path'], '/'));
            if (!empty($pathSegments[0])) {
                $gameName = filter_var($pathSegments[0], FILTER_SANITIZE_STRING); // Sanitize
            }
        }

        if (isset($urlParts['fragment'])) {
            $fragment = $urlParts['fragment'];
            $fragmentParts = preg_split('/[?&]/', $fragment, 2);
            if (!empty($fragmentParts[0])) {
                $curData = filter_var($fragmentParts[0], FILTER_SANITIZE_STRING); // Sanitize
            }
        }

        if (empty($gameName) || empty($curData)) {
            $alertType = 'error';
            $alertMessage = "Error: Could not automatically extract Game Name or Game Data from the URL. Please check the format.";
        } else {
            try {
                $tbotGameService = new TbotGameService($gameName);
                $result = $tbotGameService->sendScore($curData, $score);
                
                // ... (rest of the result handling logic from your previous index.php) ...
                 if ($result === false) {
                    $alertType = 'error';
                    $alertMessage = "Error: Failed to send score. Server might be unreachable, or data invalid for Game: '" . htmlspecialchars($gameName) . "'.";
                } elseif (is_array($result) && (isset($result['error']) || isset($result['fail']))) {
                    $alertType = 'error';
                    $alertMessage = "Server indicated an error with the request.";
                    $responseData = $result;
                } elseif (is_array($result) && !empty($result)) {
                    $alertType = 'success';
                    $alertMessage = "Score submitted successfully for game: " . htmlspecialchars($gameName) . "!";
                    $responseData = $result;
                } elseif (is_array($result) && empty($result)) {
                    $alertType = 'success';
                    $alertMessage = "Request processed successfully, but server returned an empty (but successful) response for game: " . htmlspecialchars($gameName) . ".";
                } elseif (is_string($result) && !empty($result)) {
                    $alertType = 'info';
                    $alertMessage = "Received a non-standard or non-JSON string response from the server for game: " . htmlspecialchars($gameName) . ".";
                    $responseData = $result;
                } else { 
                    $alertType = 'info';
                    $alertMessage = "Request processed for game: " . htmlspecialchars($gameName) . ". The server's response was minimal or unexpected.";
                    $responseData = $result === null ? "NULL response" : (is_string($result) ? $result : "Unexpected response type");
                }

            } catch (InvalidArgumentException $e) { // Catch specific exceptions from service if you add them
                $alertType = 'error';
                $alertMessage = "Input Error: " . htmlspecialchars($e->getMessage());
            } catch (Exception $e) { // General error
                $alertType = 'error';
                $alertMessage = "An unexpected application error occurred: " . htmlspecialchars($e->getMessage());
                // Consider logging $e->getTraceAsString() for debugging
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Tbot Game Interface - Pro</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css"> </head>
<body>
    <div class="mouse-trail">
        <canvas id="mouseTrailCanvas"></canvas>
    </div>
    <div class="container">
        <h1>Tbot Game Interface</h1>
        <form method="post" action="" id="gameSubmissionForm">
            <div class="form-group">
                <label for="full_game_url">Full Game URL:</label>
                <input type="url" id="full_game_url" name="full_game_url" placeholder="e.g., https://tbot.xyz/game_name/#data_string..." value="<?php echo htmlspecialchars($submittedUrl); ?>" required>
            </div>
            <div class="form-group">
                <label for="score">Score:</label>
                <input type="number" id="score" name="score" placeholder="Enter your score" value="<?php echo htmlspecialchars($submittedScore); ?>" required min="0">
            </div>
            <div class="form-group form-group-checkbox">
                <input type="checkbox" id="show_log" name="show_log" value="1" <?php if ($showFullLogChecked) echo 'checked'; ?>>
                <label for="show_log">Show Full Server Response Log</label>
            </div>
            <div class="form-buttons">
                <button type="submit" name="submit" class="button">Submit Score</button>
                <button type="button" id="clearFormButton" class="button button-secondary">Clear Form</button>
            </div>
        </form>
        
        <?php if (!empty($alertMessage)): ?>
            <div class="alert alert-<?php echo htmlspecialchars($alertType); ?>">
                <p><?php echo htmlspecialchars($alertMessage); ?></p>
                <?php 
                $shouldDisplayLog = ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['show_log']));
                if ($shouldDisplayLog && $responseData !== null && (is_array($responseData) ? !empty($responseData) : (is_string($responseData) && $responseData !== ''))) :
                    $responseOutput = '';
                    if (is_array($responseData)) {
                        $responseOutput = print_r($responseData, true);
                    } else {
                        $responseOutput = (string)$responseData;
                    }
                ?>
                    <div class="response-data">
                        <strong>Server Response Detail:</strong>
                        <pre><?php echo htmlspecialchars($responseOutput); ?></pre>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
    <script src="assets/js/main.js"></script> </body>
</html>