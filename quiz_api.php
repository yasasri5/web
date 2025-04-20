<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Database config
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "yoksha";

// Connect to DB
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die(json_encode(['status' => 'error', 'message' => 'Connection failed: ' . $conn->connect_error]));
}

// Handle requests
$request_method = $_SERVER['REQUEST_METHOD'];
$request_uri = $_SERVER['REQUEST_URI'];

// GET quiz
if ($request_method == 'GET' && strpos($request_uri, 'get_quiz') !== false) {
    $quiz_id = isset($_GET['id']) ? (int)$_GET['id'] : null;

    if ($quiz_id) {
        // Get quiz
        $stmt = $conn->prepare("SELECT * FROM quizzes WHERE id = ?");
        $stmt->bind_param("i", $quiz_id);
        $stmt->execute();
        $quiz_result = $stmt->get_result();

        if ($quiz_result->num_rows > 0) {
            $quiz = $quiz_result->fetch_assoc();

            // Get questions
            $stmt = $conn->prepare("SELECT id, question, option1, option2, option3, option4 FROM questions WHERE quiz_id = ?");
            $stmt->bind_param("i", $quiz_id);
            $stmt->execute();
            $questions_result = $stmt->get_result();

            $questions = [];
            while ($row = $questions_result->fetch_assoc()) {
                $questions[] = $row;
            }

            echo json_encode([
                'status' => 'success',
                'quiz' => [
                    'id' => $quiz['id'],
                    'title' => $quiz['title'],
                    'description' => $quiz['description'],
                    'questions' => $questions
                ]
            ]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Quiz not found']);
        }
    } else {
        // All quizzes
        $result = $conn->query("SELECT * FROM quizzes");
        $quizzes = [];
        while ($row = $result->fetch_assoc()) {
            $quizzes[] = $row;
        }
        echo json_encode(['status' => 'success', 'quizzes' => $quizzes]);
    }
}

// POST quiz submission
elseif ($request_method == 'POST' && strpos($request_uri, 'submit_quiz') !== false) {
    $data = json_decode(file_get_contents("php://input"), true);

    if (isset($data['user_id'], $data['quiz_id'], $data['answers']) && is_array($data['answers'])) {
        $user_id = $conn->real_escape_string($data['user_id']);
        $quiz_id = (int)$data['quiz_id'];
        $answers = $data['answers'];

        $correct = 0;
        $wrong = 0;

        foreach ($answers as $answer) {
            $question_id = (int)$answer['question_id'];
            $selected_option = (int)$answer['selected_option'];

            // Fetch correct answer
            $stmt = $conn->prepare("SELECT correct_option FROM questions WHERE id = ? AND quiz_id = ?");
            $stmt->bind_param("ii", $question_id, $quiz_id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($row = $result->fetch_assoc()) {
                if ($row['correct_option'] == $selected_option) {
                    $correct++;
                } else {
                    $wrong++;
                }
            }
        }

        $total_questions = $correct + $wrong;
        $percentage = round(($correct / $total_questions) * 100, 2);

        // Save result
        $stmt = $conn->prepare("INSERT INTO quiz_results (user_id, quiz_id, score, total_questions, percentage) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("siiid", $user_id, $quiz_id, $correct, $total_questions, $percentage);
        $stmt->execute();

        // Feedback
        if ($percentage >= 80) {
            $feedback = "🎉 Awesome job! You're a superstar!";
        } elseif ($percentage >= 60) {
            $feedback = "👍 Good work! Keep practicing!";
        } else {
            $feedback = "🤔 Nice try! You'll do better next time!";
        }

        echo json_encode([
            'status' => 'success',
            'result' => [
                'correct' => $correct,
                'wrong' => $wrong,
                'score' => $correct,
                'total' => $total_questions,
                'percentage' => $percentage,
                'feedback' => $feedback
            ]
        ]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Missing required fields or invalid answer format']);
    }
}

// Default response
else {
    echo json_encode([
        'status' => 'info',
        'message' => 'Yoksha Quiz API',
        'endpoints' => [
            'GET /get_quiz?id=1' => 'Fetch quiz and questions',
            'POST /submit_quiz' => 'Submit answers and get result (user_id, quiz_id, answers[])'
        ]
    ]);
}

$conn->close();
?>
