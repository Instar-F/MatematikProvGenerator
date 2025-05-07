<?php
// Development error reporting - but don't output errors when handling AJAX
if (isset($_GET['action'])) {
    // For AJAX requests, don't output errors
    error_reporting(E_ALL);
    ini_set('display_errors', 0);
} else {
    // For regular page loads, display errors
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}

// Start output buffering to prevent unexpected output
ob_start();

// Include header and establish database connection
try {
    
    $mysqli = new mysqli("localhost", "root", "", "matteprovgenerator");
    if ($mysqli->connect_error) {
        if (isset($_GET['action'])) {
            // Clean any output and send JSON error
            ob_clean();
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Database connection failed: ' . $mysqli->connect_error]);
            exit;
        } else {
            // Regular page error
            throw new Exception("Database connection failed: " . $mysqli->connect_error);
        }
    }
} catch (Exception $e) {
    if (isset($_GET['action'])) {
        // Clean any output and send JSON error
        ob_clean();
        header('Content-Type: application/json');
        echo json_encode(['error' => $e->getMessage()]);
        exit;
    } else {
        die("Error: " . $e->getMessage());
    }
}

// Handle AJAX requests
if (isset($_GET['action'])) {
    // Clean any previous output that might corrupt JSON
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
            $questionType = (int) $_GET['question_type'];
            $courseId = isset($_GET['course_id']) ? (int) $_GET['course_id'] : 0;
            $difficulty = isset($_GET['difficulty']) ? (int) $_GET['difficulty'] : 0;

            if ($categoryId <= 0 || $questionType <= 0) {
                echo json_encode(['error' => 'Missing or invalid parameters']);
                exit;
            }

            // Debug information
            $debug = [
                'params' => [
                    'category_id' => $categoryId,
                    'question_type' => $questionType,
                    'course_id' => $courseId,
                    'difficulty' => $difficulty
                ]
            ];

            // Build SQL based on whether difficulty is specified
            if ($difficulty > 0) {
                $sql = "SELECT qu_id, text FROM questions WHERE ca_id = ? AND qt_id = ? AND difficulty = ? AND is_active = 1 ORDER BY RAND() LIMIT 1";
                $stmt = $mysqli->prepare($sql);
                if (!$stmt) {
                    echo json_encode(['error' => 'DB prepare error: ' . $mysqli->error, 'debug' => $debug]);
                    exit;
                }
                $stmt->bind_param("iii", $categoryId, $questionType, $difficulty);
                $debug['sql'] = $sql;
            } else {
                $sql = "SELECT qu_id, text FROM questions WHERE ca_id = ? AND qt_id = ? AND is_active = 1 ORDER BY RAND() LIMIT 1";
                $stmt = $mysqli->prepare($sql);
                if (!$stmt) {
                    echo json_encode(['error' => 'DB prepare error: ' . $mysqli->error, 'debug' => $debug]);
                    exit;
                }
                $stmt->bind_param("ii", $categoryId, $questionType);
                $debug['sql'] = $sql;
            }

            // First, check if questions exist with these parameters
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 0) {
                // Check if ANY questions exist for this category and type
                $checkSql = "SELECT COUNT(*) as count FROM questions WHERE ca_id = ? AND qt_id = ? AND is_active = 1";
                $checkStmt = $mysqli->prepare($checkSql);
                $checkStmt->bind_param("ii", $categoryId, $questionType);
                $checkStmt->execute();
                $countResult = $checkStmt->get_result()->fetch_assoc();
                
                if ($countResult['count'] > 0) {
                    // Questions exist, but not with the specified difficulty
                    echo json_encode([
                        'error' => 'No questions found with the selected difficulty. Try "Any difficulty".',
                        'debug' => $debug
                    ]);
                } else {
                    // No questions at all for this category and type
                    echo json_encode([
                        'error' => 'No questions found for the selected category and question type.',
                        'debug' => $debug
                    ]);
                }
                exit;
            }

            // Question found, return it
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

// Clear the buffer for regular page output
ob_clean();

// Try to get question types
try {
    $questionTypes = $mysqli->query("SELECT qt_id, qt_name FROM questiontypes ORDER BY qt_name");
    if (!$questionTypes) {
        throw new Exception("Error fetching question types: " . $mysqli->error);
    }
    $questionTypesArray = $questionTypes->fetch_all(MYSQLI_ASSOC);
} catch (Exception $e) {
    die("Database error: " . $e->getMessage());
}

// Handle exam creation
// Handle form submission
$statusMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['questions'])) {
    try {
        $examName = trim($_POST['exam_name']) ?: "Generated Exam - " . date("Y-m-d H:i:s");
        $createdBy = isset($_SESSION['user']['id']) ? $_SESSION['user']['id'] : 1;
        $questionIds = array_filter(array_map('intval', explode(',', $_POST['questions'][0] ?? '')));

        if (empty($questionIds)) {
            $statusMessage = "<div style='color:red;'>❌ No valid questions selected.</div>";
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

            $statusMessage = "<div style='color:green;'>✅ Exam '<strong>" . htmlspecialchars($examName) . "</strong>' created with <strong>" . count($questionIds) . "</strong> questions.</div>";
        }
    } catch (Exception $e) {
        $statusMessage = "<div style='color:red;'>❌ Error creating exam: " . htmlspecialchars($e->getMessage()) . "</div>";
    }
}


require_once "include/header.php";

// Display status message if available
if (!empty($statusMessage)) {
    echo $statusMessage;
}
?>

<div class="container-fluid mt-5">
    <div class="row">
        <!-- Sidebar with links -->
        <div class="col-md-4 ps-0">
            <?php require_once "sidebar.php"; ?>
        </div>
        <!-- Main content -->
        <div class="col-md-8">
            <div class="card shadow-lg">
                <div class="card-header text-center">
                    <h2>Exam Generator</h2>
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
                            <input type="number" id="num_questions" class="form-control" min="1" max="20" value="5" onkeydown="handleNumberInput(event)">
                        </div>

                        <div class="d-grid mb-3">
                            <button type="button" class="btn btn-primary" onclick="buildQuestionSlots()">Load Questions</button>
                        </div>

                        <div id="questionSlots" class="mb-4"></div>

                        <input type="hidden" name="questions[]" id="question_ids">
                        <div class="d-grid">
                            <button type="submit" id="submitButton" class="btn btn-success">Create Exam</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Only define questionTypes if available from server
const questionTypes = <?= json_encode($questionTypesArray ?? []) ?>;
let questionData = [];
let debugMode = true; // Set to true to see debug information

function showDebug(data) {
    if (!debugMode) return;
    const debugArea = document.getElementById('debug-area');
    const debugContent = document.getElementById('debug-content');
    debugArea.style.display = 'block';
    debugContent.innerHTML = '<pre>' + JSON.stringify(data, null, 2) + '</pre>';
}

function loadCategories() {
    const courseId = document.getElementById('course_id').value;
    if (!courseId) {
        document.getElementById('category_id').innerHTML = "<option value=''>Select course first</option>";
        return;
    }

    // Show loading indicator
    document.getElementById('category_id').innerHTML = "<option value=''>Loading...</option>";
    
    fetch(`generate-test.php?action=get_categories&course_id=${courseId}`)
        .then(res => {
            if (!res.ok) {
                throw new Error(`Server returned ${res.status}: ${res.statusText}`);
            }
            return res.json();
        })
        .then(data => {
            if (data.error) {
                throw new Error(data.error);
            }
            document.getElementById('category_id').innerHTML = data.html || "<option value=''>No categories found</option>";
        })
        .catch(err => {
            console.error("Loading categories failed:", err);
            document.getElementById('category_id').innerHTML = "<option value=''>Error loading categories</option>";
            alert("❌ Could not load categories: " + err.message);
        });
}

function buildQuestionSlots() {
    const count = parseInt(document.getElementById('num_questions').value);
    if (isNaN(count) || count < 1 || count > 20) {
        alert("Please enter a number between 1 and 20.");
        return;
    }

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
        wrapper.style.marginBottom = "20px";
        wrapper.style.padding = "10px";
        wrapper.style.border = "1px solid #ddd";
        wrapper.style.borderRadius = "5px";

        const typeSelect = document.createElement('select');
        typeSelect.name = `qt_select_${i}`;
        typeSelect.innerHTML = questionTypes.map(qt => `<option value="${qt.qt_id}">${qt.qt_name}</option>`).join('');
        typeSelect.onchange = () => fetchQuestion(i);
        
        // Create difficulty select
        const difficultySelect = document.createElement('select');
        difficultySelect.name = `difficulty_select_${i}`;
        difficultySelect.innerHTML = `
            <option value="0">Any difficulty</option>
            <option value="1">Difficulty 1</option>
            <option value="2">Difficulty 2</option>
            <option value="3">Difficulty 3</option>
            <option value="4">Difficulty 4</option>
            <option value="5">Difficulty 5</option>
            <option value="6">Difficulty 6</option>
        `;
        difficultySelect.onchange = () => fetchQuestion(i);
        difficultySelect.style.marginLeft = "10px";

        const preview = document.createElement('div');
        preview.id = `preview-${i}`;
        preview.style.marginTop = "10px";
        preview.style.padding = "10px";
        preview.style.backgroundColor = "#f9f9f9";
        preview.style.borderRadius = "5px";
        preview.innerText = "No question loaded yet.";

        const rerollBtn = document.createElement('button');
        rerollBtn.type = 'button';
        rerollBtn.innerText = 'Reroll';
        rerollBtn.onclick = () => fetchQuestion(i);
        rerollBtn.style.marginTop = "5px";

        wrapper.innerHTML = `<strong>Question ${i + 1}</strong><br>`;
        wrapper.appendChild(typeSelect);
        wrapper.appendChild(difficultySelect);
        wrapper.appendChild(document.createElement('br'));
        wrapper.appendChild(preview);
        wrapper.appendChild(document.createElement('br'));
        wrapper.appendChild(rerollBtn);

        container.appendChild(wrapper);
        
        // Auto-fetch a question when the slot is created
        setTimeout(() => fetchQuestion(i), 100 * (i + 1)); // Stagger requests to prevent overloading
    }
}

function fetchQuestion(index) {
    const courseId = document.getElementById('course_id').value;
    const categoryId = document.getElementById('category_id').value;
    const qtId = document.querySelector(`[name=qt_select_${index}]`).value;
    const difficulty = document.querySelector(`[name=difficulty_select_${index}]`).value;
    
    if (!courseId || !categoryId || !qtId) {
        document.getElementById(`preview-${index}`).innerHTML = "<span style='color:#f70;'>⚠️ Please select course and category first</span>";
        return;
    }

    const preview = document.getElementById(`preview-${index}`);
    preview.innerHTML = "<em>Loading question...</em>";

    const url = `generate-test.php?action=get_random_question&course_id=${courseId}&category_id=${categoryId}&question_type=${qtId}&difficulty=${difficulty}`;
    
    console.log(`Fetching question with: ${url}`);
    
    fetch(url)
        .then(res => {
            if (!res.ok) {
                throw new Error(`Server returned ${res.status}: ${res.statusText}`);
            }
            return res.json();
        })
        .then(data => {
            // Show debug info if available
            if (data.debug) {
                showDebug(data.debug);
            }
            
            if (data && data.qu_id) {
                questionData[index] = data.qu_id;
                preview.innerHTML = `<em>${data.text}</em>`;
            } else {
                questionData[index] = null;
                preview.innerHTML = `<span style='color:red;'>❌ ${data.error || 'No question found'}</span>`;
            }
        })
        .catch(err => {
            console.error("Loading question failed:", err);
            preview.innerHTML = `<span style='color:red;'>❌ Error: ${err.message}</span>`;
        });
}

document.getElementById('examForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const validIds = questionData.filter(q => q !== null && !isNaN(q));
    if (validIds.length === 0) {
        alert("❌ No valid questions loaded. Please use 'Load Questions' first.");
        return false;
    }
    
    document.getElementById('question_ids').value = validIds.join(',');
    this.submit();
});

function handleNumberInput(event) {
    if (event.key === 'Enter') {
        event.preventDefault();
        buildQuestionSlots();
        return false;
    }
}
</script>

<?php require_once "include/footer.php"; ?>
