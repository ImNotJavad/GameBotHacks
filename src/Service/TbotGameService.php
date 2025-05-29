<?php

namespace GameService\Service; 

declare(strict_types=1); // Enforce strict type checking for better code quality

/**
 * Class TbotGameService
 *
 * This class facilitates interaction with game APIs hosted on tbot.xyz.
 * It provides methods to retrieve high scores and submit game scores based on
 * the observed client-side behavior of these games.
 *
 * The service is designed to be generic for games on tbot.xyz that follow a similar
 * API pattern for score management, using a 'curData' parameter derived from the URL hash.
 *
 * Note: This service interacts with what appear to be unofficial API endpoints
 * derived from client-side analysis. Its functionality may change or break if
 * the tbot.xyz game APIs are updated. Always use such tools responsibly and
 * in accordance with the terms of service of tbot.xyz.
 */
class TbotGameService
{
    private string $baseApiUrl = "https://tbot.xyz/api";
    private string $userAgent = "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/96.0.4664.110 Safari/537.36";
    private string $origin = "https://tbot.xyz";
    private string $referer;

    /**
     * Constructor for TbotGameService.
     *
     * @param string $gameName The name of the game, used to construct the Referer header (e.g., "corsairs", "lumber", "math").
     * This corresponds to the path segment in the game's URL.
     */
    public function __construct(string $gameName)
    {
        // Ensure gameName does not have leading/trailing slashes to build referer correctly
        $cleanedGameName = trim($gameName, '/');
        $this->referer = $this->origin . "/" . $cleanedGameName . "/";
    }

    /**
     * Retrieves high scores from a tbot.xyz game.
     * This method mirrors the functionality of the getHighScores JavaScript function
     * observed in the game's client-side code.
     *
     * @param string $curData The data string obtained from the specific game URL's hash fragment.
     * This typically contains user, game, and session information.
     * @return array|false    The decoded JSON response as an associative array if successful,
     * or false on failure or if the response is not valid JSON.
     */
    public function getHighScores(string $curData): array|false
    {
        $url = $this->baseApiUrl . "/getHighScores";
        $postData = http_build_query(['data' => $curData]);
        
        $headers = [
            "Content-Type: application/x-www-form-urlencoded",
            "User-Agent: " . $this->userAgent,
            "Origin: " . $this->origin,
            "Referer: " . $this->referer,
        ];
        
        $response = $this->makeCurlRequest($url, $postData, $headers);
        // Assuming the API always returns JSON for this endpoint on success
        if (is_array($response)) {
            return $response;
        }
        // error_log("getHighScores: Unexpected response type or error. Raw response: " . print_r($response, true));
        return false;
    }

    /**
     * Sends a score to a tbot.xyz game.
     * This method mirrors the functionality of the sendScore JavaScript function
     * observed in the game's client-side code.
     *
     * @param string $curData The data string obtained from the specific game URL's hash fragment.
     * @param int $score      The score to submit.
     * @return array|false    The decoded JSON response as an associative array if successful,
     * or false on failure or if the response is not valid JSON.
     */
    public function sendScore(string $curData, int $score): array|false
    {
        $url = $this->baseApiUrl . "/setScore";
        $postData = http_build_query([
            'data' => $curData,
            'score' => $score
        ]);

        $headers = [
            "Content-Type: application/x-www-form-urlencoded",
            "User-Agent: " . $this->userAgent,
            "Origin: " . $this->origin,
            "Referer: " . $this->referer,
        ];
        
        $response = $this->makeCurlRequest($url, $postData, $headers);
        // Assuming the API always returns JSON for this endpoint on success
        if (is_array($response)) {
            return $response;
        }
        // error_log("sendScore: Unexpected response type or error. Raw response: " . print_r($response, true));
        return false;
    }

    /**
     * Private helper function to execute cURL POST requests.
     *
     * @param string $url The URL to which the request will be made.
     * @param string $postData The URL-encoded string for the POST body.
     * @param array $headers An array of HTTP headers to include in the request.
     * @return array|string|false The decoded JSON response as an associative array,
     * the raw response string if JSON decoding fails but HTTP request was successful,
     * or false on cURL error or non-200 HTTP status.
     */
    private function makeCurlRequest(string $url, string $postData, array $headers): array|string|false
    {
        $ch = curl_init($url);
        if ($ch === false) {
            // error_log("Failed to initialize cURL for URL: " . $url);
            return false;
        }

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10); // Request timeout in seconds
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5); // Connection timeout in seconds

        // For development/debugging SSL issues, uncomment these lines.
        // IMPORTANT: Do NOT use these in a production environment as they disable SSL certificate verification.
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErrorNumber = curl_errno($ch);
        $curlErrorMessage = curl_error($ch);
        curl_close($ch);

        if ($curlErrorNumber !== 0) {
            // error_log("cURL Error for $url (Code: $curlErrorNumber): " . $curlErrorMessage);
            return false;
        }

        if ($httpCode === 200) {
            if ($response === false || $response === "") {
                 // error_log("Empty successful response for $url.");
                 return []; // Or true, or specific handling based on API expectation
            }
            $decodedResponse = json_decode((string)$response, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $decodedResponse;
            } else {
                // error_log("JSON Decode Error for $url: " . json_last_error_msg() . ". Raw response: " . $response);
                return (string)$response; // Return raw response if JSON decoding fails
            }
        } else {
            // error_log("HTTP Error for $url: $httpCode. Response: " . $response);
            return false;
        }
    }
}