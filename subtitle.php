<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Audio to Subtitles Converter</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container mt-5">
    <div class="card">
        <div class="card-header">
            <h3>Upload Audio File to Generate Subtitles</h3>
        </div>
        <div class="card-body">
            <!-- Form to Upload File -->
            <form action="" method="post" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="audioFile">Choose an audio file:</label>
                    <input type="file" name="audioFile" id="audioFile" class="form-control-file" accept="audio/*" required>
                </div>
                <button type="submit" class="btn btn-primary">Convert to Subtitles</button>
            </form>
        </div>
    </div>

    <!-- Display Subtitles -->
    <?php
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        if (isset($_FILES['audioFile']) && $_FILES['audioFile']['error'] == 0) {
            $fileTmpPath = $_FILES['audioFile']['tmp_name'];
            $fileName = $_FILES['audioFile']['name'];
            $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            $allowedExtensions = array('mp3', 'wav', 'ogg', 'flac','m4a');

            if (in_array($fileExtension, $allowedExtensions)) {
                $uploadFileDir = './uploads/';
                $dest_path = $uploadFileDir . $fileName;

                if (move_uploaded_file($fileTmpPath, $dest_path)) {
                    
                    $api_key = "e4e35d65ab3147f7bcf9e46cf87012bc";
                    $result = convertToSubtitles($dest_path, $api_key);

                    // Print the raw response for debugging
                    echo '<div class="card mt-3">
                            <div class="card-header">
                                <h4>API Response</h4>
                            </div>
                            <div class="card-body">
                                <pre>' . htmlspecialchars($result['response']) . '</pre>
                            </div>
                          </div>';

                    if ($result['success']) {
                        echo '<div class="card mt-3">
                                <div class="card-header">
                                    <h4>Generated Subtitles</h4>
                                </div>
                                <div class="card-body">
                                    <textarea class="form-control" rows="10">' . htmlspecialchars($result['transcription']) . '</textarea>
                                </div>
                              </div>';
                    } else {
                        echo '<div class="alert alert-danger mt-3">Error in converting audio to subtitles.</div>';
                    }
                } else {
                    echo '<div class="alert alert-danger mt-3">There was an error moving the uploaded file.</div>';
                }
            } else {
                echo '<div class="alert alert-danger mt-3">File type not allowed.</div>';
            }
        } else {
            echo '<div class="alert alert-danger mt-3">No file uploaded or there was an upload error.</div>';
        }
    }

    function convertToSubtitles($filePath, $api_key) {
        $url = "https://api.assemblyai.com/v2/transcript";
    
        // Step 1: Upload the file to AssemblyAI
        $uploadUrl = "https://api.assemblyai.com/v2/upload";
        $ch = curl_init($uploadUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: ' . $api_key,
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, file_get_contents($filePath));
        $response = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);
    
        if ($error) {
            return [
                'success' => false,
                'response' => 'File Upload Error: ' . $error
            ];
        }
    
        $response_array = json_decode($response, true);
    
        if (!isset($response_array['upload_url'])) {
            return [
                'success' => false,
                'response' => 'File Upload Failed: ' . $response
            ];
        }
    
        $uploadUrl = $response_array['upload_url'];
    
        // Step 2: Request transcription
        $transcriptRequest = [
            'audio_url' => $uploadUrl,
            'language_code' => 'de'
        ];
    
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: ' . $api_key,
            'Content-Type: application/json',
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($transcriptRequest));
        $response = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);
    
        if ($error) {
            return [
                'success' => false,
                'response' => 'Transcription Request Error: ' . $error
            ];
        }
    
        $response_array = json_decode($response, true);
    
        if (!isset($response_array['id'])) {
            return [
                'success' => false,
                'response' => 'Transcription Request Failed: ' . $response
            ];
        }
    
        $transcriptId = $response_array['id'];
    
        // Step 3: Poll for transcription completion
        $pollUrl = $url . '/' . $transcriptId;
        do {
            sleep(5); // Wait for 5 seconds before polling again
            $ch = curl_init($pollUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Authorization: ' . $api_key,
            ]);
            $response = curl_exec($ch);
            curl_close($ch);
            $response_array = json_decode($response, true);
        } while ($response_array['status'] !== 'completed' && $response_array['status'] !== 'failed');
    
        if ($response_array['status'] === 'failed') {
            return [
                'success' => false,
                'response' => 'Transcription Failed: ' . $response
            ];
        }
    
        // Return the transcription
        return [
            'success' => true,
            'transcription' => $response_array['text'],
            'response' => $response
        ];
    }
    
    ?>
</div>

<!-- Bootstrap JS, Popper.js, and jQuery -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
