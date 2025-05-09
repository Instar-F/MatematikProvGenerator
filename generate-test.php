<?php
// Development error reporting - but don't output errors when handling AJAX
if (isset($_GET['action'])) {
    error_reporting(E_ALL);
    ini_set('display_errors', 0);
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}

ob_start();

try {
    $mysqli = new mysqli("localhost", "root", "", "matteprovgenerator");
    if ($mysqli->connect_error) {
        if (isset($_GET['action'])) {
            ob_clean();
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Database connection failed: ' . $mysqli->connect_error]);
            exit;
        } else {
            throw new Exception("Database connection failed: " . $mysqli->connect_error);
        }
    }
} catch (Exception $e) {
    if (isset($_GET['action'])) {
        ob_clean();
        header('Content-Type: application/json');
        echo json_encode(['error' => $e->getMessage()]);
        exit;
    } else {
        die("Error: " . $e->getMessage());
    }
}

if (isset($_GET['action'])) {
    ob_clean();
    header('Content-Type: application/json');

    try {
        if ($_GET['action'] === 'get_categories') {
            $courseId = (int) $_GET['course_id'];
            $stmt = $mysqli->prepare("SELECT ca_id, ca_name FROM categories WHERE ca_co_fk = ? ORDER BY ca_name");
            $stmt->bind_param("i", $courseId);
            $stmt->execute();
            $result = $stmt->get_result();
            $options = "<option value=''>Select category</option>";
            while ($row = $result->fetch_assoc()) {
                $options .= "<option value='{$row['ca_id']}'>" . htmlspecialchars($row['ca_name']) . "</option>";
            }
            echo json_encode(['html' => $options]);
            exit;
        }

        if ($_GET['action'] === 'get_random_question') {
            $categoryId = (int) $_GET['category_id'];
            $courseId = isset($_GET['course_id']) ? (int) $_GET['course_id'] : 0;
            $difficulty = isset($_GET['difficulty']) ? (int) $_GET['difficulty'] : 0;

            if ($categoryId <= 0) {
                echo json_encode(['error' => 'Missing or invalid parameters']);
                exit;
            }

            $debug = [
                'params' => [
                    'category_id' => $categoryId,
                    'course_id' => $courseId,
                    'difficulty' => $difficulty
                ]
            ];

            if ($difficulty > 0) {
                $sql = "SELECT qu_id, text FROM questions WHERE ca_id = ? AND difficulty = ? AND is_active = 1 ORDER BY RAND() LIMIT 1";
                $stmt = $mysqli->prepare($sql);
                if (!$stmt) {
                    echo json_encode(['error' => 'DB prepare error: ' . $mysqli->error, 'debug' => $debug]);
                    exit;
                }
                $stmt->bind_param("ii", $categoryId, $difficulty);
            } else {
                $sql = "SELECT qu_id, text FROM questions WHERE ca_id = ? AND is_active = 1 ORDER BY RAND() LIMIT 1";
                $stmt = $mysqli->prepare($sql);
                if (!$stmt) {
                    echo json_encode(['error' => 'DB prepare error: ' . $mysqli->error, 'debug' => $debug]);
                    exit;
                }
                $stmt->bind_param("i", $categoryId);
            }

            $debug['sql'] = $sql;

            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 0) {
                $checkSql = "SELECT COUNT(*) as count FROM questions WHERE ca_id = ? AND is_active = 1";
                $checkStmt = $mysqli->prepare($checkSql);
                $checkStmt->bind_param("i", $categoryId);
                $checkStmt->execute();
                $countResult = $checkStmt->get_result()->fetch_assoc();
                
                if ($countResult['count'] > 0) {
                    echo json_encode([
                        'error' => 'No questions found with the selected difficulty. Try "Any difficulty".',
                        'debug' => $debug
                    ]);
                } else {
                    echo json_encode([
                        'error' => 'No questions found for the selected category.',
                        'debug' => $debug
                    ]);
                }
                exit;
            }

            $question = $result->fetch_assoc();
            echo json_encode(array_merge($question, ['debug' => $debug]));
            exit;
        }

        echo json_encode(['error' => 'Unknown action']);
    } catch (Exception $e) {
        echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
    }
    exit;
}

ob_clean();

$statusMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['questions'])) {
    try {
        $examName = trim($_POST['exam_name']) ?: "Generated Exam - " . date("Y-m-d H:i:s");
        $createdBy = isset($_SESSION['user']['id']) ? $_SESSION['user']['id'] : 1;
        $questionIds = array_filter(array_map('intval', explode(',', $_POST['questions'][0] ?? '')));

        if (empty($questionIds)) {
            $statusMessage = "<div style='color:red;'>No valid questions selected.</div>";
        } else {
            $stmt = $mysqli->prepare("INSERT INTO exams (ex_name, ex_createdby_fk) VALUES (?, ?)");
            $stmt->bind_param("si", $examName, $createdBy);
            $stmt->execute();
            $examId = $stmt->insert_id;
            $stmt->close();

            foreach ($questionIds as $order => $qid) {
                $check = $mysqli->prepare("SELECT qu_id FROM questions WHERE qu_id = ?");
                $check->bind_param("i", $qid);
                $check->execute();
                if ($check->get_result()->num_rows > 0) {
                    $insert = $mysqli->prepare("INSERT INTO exam_questions (ex_id, qu_id, question_order) VALUES (?, ?, ?)");
                    $orderIndex = $order + 1;
                    $insert->bind_param("iii", $examId, $qid, $orderIndex);
                    $insert->execute();
                    $insert->close();
                }
            }

            $statusMessage = "<div style='color:green;'>Exam '<strong>" . htmlspecialchars($examName) . "</strong>' created with <strong>" . count($questionIds) . "</strong> questions.</div>";
        }
    } catch (Exception $e) {
        $statusMessage = "<div style='color:red;'>Error creating exam: " . htmlspecialchars($e->getMessage()) . "</div>";
    }
}

require_once "include/header.php";

if (!empty($statusMessage)) {
    echo $statusMessage;
}
?>

<div class="container-fluid mt-5">
    <div class="row">
        <div class="col-md-4 ps-0">
            <?php require_once "sidebar.php"; ?>
        </div>
        <div class="col-md-8">
            <div class="card shadow-lg">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Exam Generator</h4>
                </div>
                <div class="card-body">
                    <form id="examForm" method="POST">
                        <div class="mb-3">
                            <label for="exam_name" class="form-label">Exam Name:</label>
                            <input type="text" name="exam_name" id="exam_name" class="form-control" required placeholder="Enter exam name">
                        </div>

                        <div class="mb-3">
                            <label for="course_id" class="form-label">Course:</label>
                            <select name="course_id" id="course_id" class="form-select" required onchange="loadCategories()">
                                <option value="">Select course</option>
                                <?php
                                try {
                                    $courses = $mysqli->query("SELECT co_id, co_name FROM courses ORDER BY co_name");
                                    if (!$courses) {
                                        throw new Exception("Error fetching courses: " . $mysqli->error);
                                    }
                                    foreach ($courses as $c): ?>
                                        <option value="<?= $c['co_id'] ?>"><?= htmlspecialchars($c['co_name']) ?></option>
                                    <?php endforeach;
                                } catch (Exception $e) {
                                    echo "<option value=''>Error loading courses: " . htmlspecialchars($e->getMessage()) . "</option>";
                                }
                                ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="category_id" class="form-label">Category:</label>
                            <select name="category_id" id="category_id" class="form-select" required>
                                <option value="">Select course first</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="num_questions" class="form-label">Number of Questions:</label>
                            <div class="input-group">
                                <input type="number" id="num_questions" class="form-control" min="1" max="20" value="6">
                                <button type="button" class="btn btn-outline-secondary" onclick="buildQuestionSlots()">➕ Load Questions</button>
                            </div>
                        </div>

                        <div id="questionSlots" class="mb-3"></div>
                        <input type="hidden" name="questions[]" id="question_ids">

                        <div class="d-grid">
                            <button type="submit" id="submitButton" class="btn btn-success">Create Exam</button>
                        </div>
                    </form>
                </div>
                <div class="card-footer text-muted text-center">
                    <small>MatematikProvGenerator - Exam Creation Tool</small>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const defaultNumQuestions = 6;
    const defaultDifficulties = [1, 2, 3, 4, 5, 6];
    let questionData = [];
    let debugMode = true;

    window.loadCategories = function () {
        const courseId = document.getElementById('course_id').value;
        const categorySelect = document.getElementById('category_id');

        if (!courseId) {
            categorySelect.innerHTML = "<option value=''>Select course first</option>";
            return;
        }

        categorySelect.innerHTML = "<option value=''>Loading...</option>";

        fetch(`generate-test.php?action=get_categories&course_id=${courseId}`)
            .then(res => res.json())
            .then(data => {
                categorySelect.innerHTML = data.html || "<option value=''>No categories found</option>";
            })
            .catch(err => {
                categorySelect.innerHTML = "<option value=''>Error loading categories</option>";
                alert("Could not load categories: " + err.message);
            });
    };

    window.buildQuestionSlots = function () {
        const count = defaultNumQuestions;
        const courseId = document.getElementById('course_id').value;
        const categoryId = document.getElementById('category_id').value;

        if (!courseId || !categoryId) {
            alert("Please select both course and category first.");
            return;
        }

        const container = document.getElementById('questionSlots');
        container.innerHTML = '';
        questionData = Array(count).fill(null);

        for (let i = 0; i < count; i++) {
            const wrapper = document.createElement('div');
            wrapper.id = `question-block-${i}`;
            wrapper.style = "margin-bottom:20px;padding:10px;border:1px solid #ddd;border-radius:5px;";

            const difficultySelect = document.createElement('select');
            difficultySelect.name = `difficulty_select_${i}`;
            difficultySelect.innerHTML = `
                <option value="0">Any</option>
                <option value="1">1</option>
                <option value="2">2</option>
                <option value="3">3</option>
                <option value="4">4</option>
                <option value="5">5</option>
                <option value="6">6</option>
            `;
            difficultySelect.value = defaultDifficulties[i];

            const preview = document.createElement('div');
            preview.id = `preview-${i}`;
            preview.style = "margin-top:10px;padding:10px;background-color:#f9f9f9;border-radius:5px;";
            preview.innerText = "No question loaded yet.";

            const rerollBtn = document.createElement('button');
            rerollBtn.type = 'button';
            rerollBtn.innerText = '🔄 Reroll';
            rerollBtn.style = "margin-top:5px;";
            rerollBtn.onclick = () => fetchQuestion(i);

            wrapper.innerHTML = `<strong>Question ${i + 1}</strong><br>`;
            wrapper.appendChild(document.createTextNode("Difficulty: "));
            wrapper.appendChild(difficultySelect);
            wrapper.appendChild(document.createElement('br'));
            wrapper.appendChild(preview);
            wrapper.appendChild(document.createElement('br'));
            wrapper.appendChild(rerollBtn);
            container.appendChild(wrapper);

            setTimeout(() => fetchQuestion(i), 100 * (i + 1));
        }
    };

    function fetchQuestion(index) {
        const courseId = document.getElementById('course_id').value;
        const categoryId = document.getElementById('category_id').value;
        const difficulty = document.querySelector(`[name="difficulty_select_${index}"]`).value;
        const preview = document.getElementById(`preview-${index}`);

        if (!courseId || !categoryId) {
            preview.innerHTML = "<span style='color:#f70;'>⚠️ Please select course and category first</span>";
            return;
        }

        preview.innerHTML = "Loading question...";

        const url = `generate-test.php?action=get_random_question&course_id=${courseId}&category_id=${categoryId}&difficulty=${difficulty}`;

        fetch(url)
            .then(res => res.json())
            .then(data => {
                if (data && data.qu_id) {
                    questionData[index] = data.qu_id;
                    preview.innerHTML = `<em>${data.text}</em>`;
                } else {
                    questionData[index] = null;
                    preview.innerHTML = `<span style='color:red;'>${data.error || 'No question found'}</span>`;
                }

                document.getElementById('question_ids').value = questionData.filter(id => id).join(',');
            })
            .catch(err => {
                preview.innerHTML = `<span style='color:red;'>Error: ${err.message}</span>`;
            });
    }
});
</script>

<?php require_once "include/footer.php"; ?>
