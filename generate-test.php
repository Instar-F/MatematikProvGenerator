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
            $search = isset($_GET['search']) ? trim($_GET['search']) : '';

            if ($categoryId <= 0) {
                echo json_encode(['error' => 'Missing or invalid parameters']);
                exit;
            }

            // --- SEARCH/LIST MODE ---
            if (isset($_GET['list']) || $search !== '') {
                $sql = "SELECT qu_id, text, image_url, image_size, image_location FROM questions WHERE ca_id = ?";
                $params = [$categoryId];
                $types = "i";
                if ($difficulty > 0) {
                    $sql .= " AND difficulty = ?";
                    $params[] = $difficulty;
                    $types .= "i";
                }
                if ($search !== '') {
                    $sql .= " AND text LIKE ?";
                    $params[] = "%$search%";
                    $types .= "s";
                }
                $sql .= " AND is_active = 1 GROUP BY text ORDER BY RAND() LIMIT 5";
                $stmt = $mysqli->prepare($sql);
                $stmt->bind_param($types, ...$params);
                $stmt->execute();
                $result = $stmt->get_result();
                $questions = [];
                while ($row = $result->fetch_assoc()) {
                    $questions[] = $row;
                }
                echo json_encode(['questions' => $questions]);
                exit;
            }
            // --- END SEARCH/LIST MODE ---

            if ($difficulty > 0) {
                $sql = "SELECT qu_id, text, image_url, image_size, image_location FROM questions WHERE ca_id = ? AND difficulty = ? AND is_active = 1 ORDER BY RAND() LIMIT 1";
                $stmt = $mysqli->prepare($sql);
                if (!$stmt) {
                    echo json_encode(['error' => 'DB prepare error: ' . $mysqli->error, 'debug' => $debug]);
                    exit;
                }
                $stmt->bind_param("ii", $categoryId, $difficulty);
            } else {
                $sql = "SELECT qu_id, text, image_url, image_size, image_location FROM questions WHERE ca_id = ? AND is_active = 1 ORDER BY RAND() LIMIT 1";
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

if (!$user_obj->checkLoginStatus($_SESSION['user']['id'])) {
    header("Location: login.php");
    exit();
}

$result = $user_obj->checkUserRole($_SESSION['user']['role'], 100);

if (!$result) {
    echo "You do not have the rights to access this page.";
    exit(); // Stop the script from continuing
}

if (!empty($statusMessage)) {
    echo $statusMessage;
}

$duplicateExamData = null;
$duplicateQuestions = [];
$duplicateCourseId = null;
$duplicateCategoryId = null;

if (isset($_GET['duplicate']) && is_numeric($_GET['duplicate'])) {
    $duplicateId = (int)$_GET['duplicate'];

    // Load exam basic info AND questions in one efficient query
    $stmt = $mysqli->prepare("
        SELECT 
            e.ex_id,
            e.ex_name,
            q.qu_id,
            q.text,
            q.difficulty,
            q.ca_id,
            c.ca_name,
            c.ca_co_fk AS course_id,
            co.co_name AS course_name,
            eq.question_order
        FROM exams e
        JOIN exam_questions eq ON e.ex_id = eq.ex_id
        JOIN questions q ON eq.qu_id = q.qu_id
        JOIN categories c ON q.ca_id = c.ca_id
        JOIN courses co ON c.ca_co_fk = co.co_id
        WHERE e.ex_id = ?
        ORDER BY eq.question_order ASC
    ");
    
    $stmt->bind_param("i", $duplicateId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $duplicateQuestions = [];
        $duplicateQuestionTexts = [];
        $duplicateQuestionDifficulties = []; // Add this line
        $row = $result->fetch_assoc();
        
        // Set exam data from first row with (duplicate) added
        $duplicateExamData = [
            'ex_id' => $row['ex_id'],
            'ex_name' => $row['ex_name'] . ' (duplicate)'
        ];
        
        // Set course and category from first row
        $duplicateCategoryId = $row['ca_id'];
        $duplicateCourseId = $row['course_id'];
        
        // Add first question with difficulty
        $duplicateQuestions[] = $row['qu_id'];
        $duplicateQuestionTexts[$row['qu_id']] = $row['text'];
        $duplicateQuestionDifficulties[$row['qu_id']] = $row['difficulty']; // Add this line

        // Add remaining questions
        while ($row = $result->fetch_assoc()) {
            $duplicateQuestions[] = $row['qu_id'];
            $duplicateQuestionTexts[$row['qu_id']] = $row['text'];
            $duplicateQuestionDifficulties[$row['qu_id']] = $row['difficulty']; // Add this line
        }
    }
}

// Add this function at the top, after opening PHP tag and before any HTML output
function autoWrapLatex($text) {
    // 1. Wrap <p>...</p> containing only a LaTeX command (e.g. \frac{...}{...}) with $$
    $text = preg_replace_callback(
        '/<p>\s*(\\\\[a-zA-Z]+(?:\{[^}]+\})+)\s*<\/p>/',
        function($matches) {
            return '<p>$$' . $matches[1] . '$$</p>';
        },
        $text
    );
    // 2. Wrap bare LaTeX commands on their own line (not already inside $$...$$)
    $text = preg_replace_callback(
        '/(^|[\s>])\\\\([a-zA-Z]+(?:\{[^}]+\})+)($|[\s<])/',
        function($matches) {
            return $matches[1] . '$$\\' . $matches[2] . '$$' . $matches[3];
        },
        $text
    );
    // 3. Wrap lines that look like math formulas (e.g. W = F \cdot s) with $$
    $text = preg_replace_callback(
        '/(^|\n)([A-Za-z0-9_\\\{\}\^\s\=\+\-\*\/\(\)\.,:;·\[\]\\\\]+=[^<\n]+)(\n|$)/m',
        function($matches) {
            // Only wrap if not already inside $$
            if (strpos($matches[2], '$$') === false) {
                return $matches[1] . '$$' . trim($matches[2]) . '$$' . $matches[3];
            }
            return $matches[0];
        },
        $text
    );
    return $text;
}
?>

<style>
#sidebarColumn {
    position: fixed;
    top: 0;
    left: 0;
    width: 320px;
    height: 100vh;
    background: #fff;
    z-index: 2000;
    box-shadow: 2px 0 16px rgba(0,0,0,0.12);
    transform: translateX(-100%);
    opacity: 0;
    transition: transform 0.28s cubic-bezier(.4,0,.2,1), opacity 0.18s cubic-bezier(.4,0,.2,1);
    will-change: transform, opacity;
}
#sidebarColumn.visible {
    transform: translateX(0);
    opacity: 1;
}
#mainColumn {
    transition: none;
}
.page-centered-container {
    max-width: 1100px;
    margin: 0 auto;
    padding: 32px 16px 32px 16px;
}
/* Sidebar toggle button styling */
#toggleSidebar {
    position: fixed;
    top: 38%; /* Move above the vertical center */
    left: 0;
    z-index: 2100;
    border-radius: 50%;
    width: 44px;
    height: 44px;
    padding: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #fff;
    border: 2px solid #0d6efd;
    color: #0d6efd;
    box-shadow: 0 2px 8px rgba(0,0,0,0.15);
    transition: left 0.28s cubic-bezier(.4,0,.2,1), background 0.18s, color 0.18s;
    cursor: pointer;
}
#toggleSidebar.open {
    left: 320px;
}
#toggleSidebar.closed {
    left: 0;
}
#toggleSidebar:hover {
    background: #e7f1ff;
    color: #0a58ca;
}
#toggleArrow {
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    width: 22px;
    height: 22px;
    font-size: 1.7rem;
    transition: color 0.18s;
    position: relative;
}
.hamburger-bar {
    width: 22px;
    height: 3px;
    background: #0d6efd;
    margin: 2.5px 0;
    border-radius: 2px;
    transition: all 0.25s;
}
#toggleSidebar.open .hamburger-bar {
    display: none;
}
.sidebar-close-icon {
    display: none;
    font-size: 1.7rem;
    color: #0d6efd;
    line-height: 1;
    width: 100%;
    height: 100%;
    align-items: center;
    justify-content: center;
}
#toggleSidebar.open .sidebar-close-icon {
    display: flex;
    align-items: center;
    justify-content: center;
}
#sidebarOverlay {
    display: none;
    position: fixed;
    z-index: 1999;
    top: 0;
    left: 0;
    width: 100vw;
    height: 100vh;
    background: rgba(0,0,0,0.18);
    transition: opacity 0.18s;
}
#sidebarOverlay.visible {
    display: block;
    opacity: 1;
}
</style>
<button id="toggleSidebar" aria-label="Toggle Sidebar" type="button" class="closed">
    <span id="toggleArrow">
        <span class="hamburger-bar"></span>
        <span class="hamburger-bar"></span>
        <span class="hamburger-bar"></span>
        <span class="sidebar-close-icon">
            <svg width="22" height="22" viewBox="0 0 22 22" fill="none" style="display:block;margin:auto;" xmlns="http://www.w3.org/2000/svg">
                <rect x="6" y="10" width="10" height="2" rx="1" fill="#0d6efd"/>
            </svg>
        </span>
    </span>
</button>
<div id="sidebarOverlay"></div>

<div class="container-fluid page-centered-container mt-5">
    <div class="row" id="contentRow">
        <!-- Sidebar with links -->
        <div class="col-md ps-0" id="sidebarColumn">
            <?php require_once "sidebar.php"; ?>
        </div>
        <!-- Main content -->
        <div class="col-md" id="mainColumn">
            <div class="card shadow-lg">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Exam Generator</h4>
                </div>
                <div class="card-body">
                    <form id="examForm" method="POST">
                        <div class="mb-3">
                            <label for="exam_name" class="form-label">Exam Name:</label>
                            <input type="text" name="exam_name" id="exam_name" class="form-control" required placeholder="Enter exam name" value="<?= htmlspecialchars($duplicateExamData['ex_name'] ?? '') ?>">
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
                            <input type="number" 
                                   id="num_questions" 
                                   class="form-control" 
                                   min="1" 
                                   max="20" 
                                   step="1"
                                   value="6" 
                                   onchange="handleNumberChange(event)"
                                   onkeydown="if(event.key === 'Enter') { event.preventDefault(); this.blur(); }">
                        </div>

                        <div id="questionSlots" class="mb-3"></div>
                        <input type="hidden" name="questions[]" id="question_ids">
                        <!-- DUPLICATE MODE SCRIPT MOVED BELOW -->

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
// Declare questionData in the global scope
window.questionData = [];

// Add this new function to handle number changes
function handleNumberChange(event) {
    const courseId = document.getElementById('course_id').value;
    const categoryId = document.getElementById('category_id').value;
    
    if (courseId && categoryId) {
        // Always preserve existing when changing number
        buildQuestionSlots(true);
    }
}

document.addEventListener("DOMContentLoaded", function () {
    const defaultNumQuestions = 6; // One for each difficulty level
    const defaultDifficulties = [1, 2, 3, 4, 5, 6]; // Default difficulties in order
    let debugMode = true;

    // Replace the loadCategories function and add auto-build functionality
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

    // Add event listeners for auto-building slots
    document.getElementById('category_id').addEventListener('change', function() {
        const courseId = document.getElementById('course_id').value;
        const categoryId = this.value;
        
        if (courseId && categoryId) {
            buildQuestionSlots();
        }
    });

    window.buildQuestionSlots = function (preserveExisting = false) {
        const count = parseInt(document.getElementById('num_questions').value, 10) || defaultNumQuestions;
        const courseId = document.getElementById('course_id').value;
        const categoryId = document.getElementById('category_id').value;
        
        if (!courseId || !categoryId) {
            alert("Please select both course and category first.");
            return;
        }

        const container = document.getElementById('questionSlots');
        // Save existing questions before clearing container
        const oldQuestionData = [...window.questionData];
        
        container.innerHTML = '';
        // Initialize new array with existing data where available
        window.questionData = new Array(count).fill(null).map((_, i) => 
            preserveExisting && i < oldQuestionData.length ? oldQuestionData[i] : null
        );

        // Add new question button at the top
        const addButton = document.createElement('button');
        addButton.type = 'button';
        addButton.className = 'btn btn-primary mb-3';
        addButton.innerHTML = '➕ Add Question';
        addButton.onclick = () => {
            const newCount = window.questionData.length + 1;
            document.getElementById('num_questions').value = newCount;
            window.questionData.push(null);
            addQuestionSlot(newCount - 1, false); // New slot should not preserve
        };
        container.appendChild(addButton);

        // Create question slots
        for (let i = 0; i < count; i++) {
            addQuestionSlot(i, preserveExisting);
        }

        // Update the hidden input with question IDs
        document.getElementById('question_ids').value = window.questionData.filter(id => id).join(',');
    };

    // Update the addQuestionSlot function definition
    function addQuestionSlot(index, preserveExisting = false) {
        const container = document.getElementById('questionSlots');
        const courseId = document.getElementById('course_id').value;
        const categoryId = document.getElementById('category_id').value;
        
        const wrapper = document.createElement('div');
        wrapper.id = `question-block-${index}`;
        wrapper.className = 'question-slot mb-3 p-3 border rounded';

        const difficultySelect = document.createElement('select');
        difficultySelect.name = `difficulty_select_${index}`;
        difficultySelect.className = 'form-select d-inline-block w-auto me-2';
        difficultySelect.innerHTML = `
            <option value="0">Any</option>
            <option value="1">1</option>
            <option value="2">2</option>
            <option value="3">3</option>
            <option value="4">4</option>
            <option value="5">5</option>
            <option value="6">6</option>
        `;

        // Set default difficulty based on index
        if (!preserveExisting && index < defaultDifficulties.length) {
            difficultySelect.value = defaultDifficulties[index];
            // Automatically fetch a question with this difficulty
            setTimeout(() => fetchQuestion(index), 100);
        }

        const preview = document.createElement('div');
        preview.id = `preview-${index}`;
        preview.className = 'mt-2 p-2 bg-light rounded';
        preview.innerText = "Click search or reroll to load a question";

        // Search UI
        const controlsDiv = document.createElement('div');
        controlsDiv.className = 'd-flex gap-2 align-items-center position-relative mt-2';

        const searchWrapper = document.createElement('div');
        searchWrapper.className = 'input-group';
        searchWrapper.style = 'max-width: 300px;';

        const searchInput = document.createElement('input');
        searchInput.type = 'text';
        searchInput.placeholder = 'Search questions...';
        searchInput.className = 'form-control';

        const searchButton = document.createElement('button');
        searchButton.type = 'button';
        searchButton.className = 'btn btn-outline-secondary';
        searchButton.innerHTML = '🔍';
        searchButton.onclick = () => loadQuestions(searchInput.value.trim());

        const rerollBtn = document.createElement('button');
        rerollBtn.type = 'button';
        rerollBtn.className = 'btn btn-outline-secondary';
        rerollBtn.innerHTML = '🔄';
        rerollBtn.onclick = () => fetchQuestion(index);

        const removeBtn = document.createElement('button');
        removeBtn.type = 'button';
        removeBtn.className = 'btn btn-outline-danger';
        removeBtn.innerHTML = '❌';
        removeBtn.onclick = () => {
            wrapper.remove();
            window.questionData[index] = null;
            document.getElementById('question_ids').value = window.questionData.filter(id => id).join(',');
        };

        const searchResults = document.createElement('div');
        searchResults.className = 'search-results position-absolute bg-white border rounded';
        searchResults.style = 'display:none; top:100%; left:0; z-index:1000; width:300px; max-height:200px; overflow-y:auto; box-shadow:0 2px 4px rgba(0,0,0,0.1);';

        // Prevent form submission on enter
        searchInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                loadQuestions(this.value.trim());
            }
        });

        // Move renderPreview to the outer scope so it's available everywhere
        window.renderPreview = function(q) {
            let html = `<em>${q.text}</em>`;
            if (q.image_url) {
                const size = q.image_size && !isNaN(q.image_size) ? parseInt(q.image_size) : 100;
                const style = `max-width:${size}%;height:auto;`;
                const imgTag = `<img src="${q.image_url}" style="${style}" />`;
                const loc = parseInt(q.image_location || 0);
                if (loc === 3) { // above
                    html = imgTag + "<br>" + html;
                } else if (loc === 4) { // below
                    html = html + "<br>" + imgTag;
                } else if (loc === 2) { // left
                    html = `<div style="display:flex;align-items:center;"><div class="me-2">${imgTag}</div><div>${html}</div></div>`;
                } else if (loc === 1) { // right
                    html = `<div style="display:flex;align-items:center;"><div>${html}</div><div class="ms-2">${imgTag}</div></div>`;
                }
            }
            return html;
        };

        function loadQuestions(searchTerm = '') {
            const courseId = document.getElementById('course_id').value;
            const categoryId = document.getElementById('category_id').value;
            const difficulty = difficultySelect.value;
            
            fetch(`generate-test.php?action=get_random_question&course_id=${courseId}&category_id=${categoryId}&difficulty=${difficulty}&search=${encodeURIComponent(searchTerm)}&list=1`)
                .then(res => res.json())
                .then(data => {
                    searchResults.innerHTML = '';
                    if (data.questions?.length > 0) {
                        searchResults.style.display = 'block';
                        data.questions.forEach(q => {
                            const div = document.createElement('div');
                            div.className = 'p-2 border-bottom hover-bg-light cursor-pointer';
                            div.innerHTML = window.renderPreview(q);
                            div.onclick = () => {
                                window.questionData[index] = q.qu_id;
                                preview.innerHTML = window.renderPreview(q);
                                document.getElementById('question_ids').value = window.questionData.filter(id => id).join(',');
                                searchResults.style.display = 'none';
                                searchInput.value = '';
                            };
                            searchResults.appendChild(div);
                        });
                    }
                });
        }

        searchWrapper.append(searchInput, searchButton);
        controlsDiv.append(searchWrapper, rerollBtn, removeBtn, searchResults);

        wrapper.innerHTML = `<strong>Question ${index + 1}</strong><br>`;
        wrapper.appendChild(document.createTextNode("Difficulty: "));
        wrapper.appendChild(difficultySelect);
        wrapper.appendChild(preview);
        wrapper.appendChild(controlsDiv);

        // Insert after the Add Question button
        container.insertBefore(wrapper, container.children[index + 1]);

        // Update the preview loading section
        if (preserveExisting && window.questionData[index]) {
            const storedQuestionId = window.questionData[index];
            if (typeof questionTexts !== 'undefined' && questionTexts[storedQuestionId]) {
                // Use cached text if available (for duplicate mode)
                preview.innerHTML = `<em>${questionTexts[storedQuestionId]}</em>`;
            } else {
                // Fetch from server if needed
                fetch(`generate-test.php?action=get_random_question&category_id=${categoryId}&qu_id=${storedQuestionId}`)
                    .then(res => res.json())
                    .then(data => {
                        if (data && data.text) {
                            preview.innerHTML = `<em>${data.text}</em>`;
                        }
                    });
            }
        }
    }

    // Add fetchQuestion function
    function fetchQuestion(index) {
        const courseId = document.getElementById('course_id').value;
        const categoryId = document.getElementById('category_id').value;
        const difficulty = document.querySelector(`[name="difficulty_select_${index}"]`).value;
        const preview = document.getElementById(`preview-${index}`);

        if (!preview) return;

        preview.innerHTML = "Loading...";

        fetch(`generate-test.php?action=get_random_question&course_id=${courseId}&category_id=${categoryId}&difficulty=${difficulty}`)
            .then(res => res.json())
            .then(function(data) {
                if (data && data.qu_id) {
                    window.questionData[index] = data.qu_id;
                    preview.innerHTML = renderPreview(data);
                    document.getElementById('question_ids').value = window.questionData.filter(id => id).join(',');
                } else {
                    preview.innerHTML = `<span style='color:red;'>${data.error || 'No question found'}</span>`;
                }
            })
            .catch(err => {
                preview.innerHTML = `<span style='color:red;'>Error: ${err.message}</span>`;
            });
    }

    // Initial load: set course and category if in duplicate mode
    <?php if (!empty($duplicateQuestions)): ?>
    const questionIds = <?= json_encode($duplicateQuestions) ?>;
    const questionTexts = <?= json_encode($duplicateQuestionTexts) ?>;
    const questionDifficulties = <?= json_encode($duplicateQuestionDifficulties) ?>; // Add this line
    const duplicateCourseId = <?= isset($duplicateCourseId) ? (int)$duplicateCourseId : 'null' ?>;
    const duplicateCategoryId = <?= isset($duplicateCategoryId) ? (int)$duplicateCategoryId : 'null' ?>;

    // Set the number of questions
    document.getElementById('num_questions').value = questionIds.length;
    
    if (duplicateCourseId && duplicateCategoryId) {
        // First set course and trigger category load
        document.getElementById('course_id').value = duplicateCourseId;
        loadCategories();

        // Wait for categories to load
        setTimeout(() => {
            document.getElementById('category_id').value = duplicateCategoryId;
            
            // Disable after setting values
            document.getElementById('course_id').disabled = true;
            document.getElementById('category_id').disabled = true;
            
            buildQuestionSlots();

            // Wait for slots to be built
            setTimeout(() => {
                window.questionData = new Array(questionIds.length); // Reset questionData
                
                // Fill in the questions from the database
                for (let i = 0; i < questionIds.length; i++) {
                    const preview = document.getElementById(`preview-${i}`);
                    const qid = questionIds[i];
                    if (preview && questionTexts[qid]) {
                        preview.innerHTML = `<em>${questionTexts[qid]}</em>`;
                        window.questionData[i] = qid;
                        
                        // Update difficulty select to show correct difficulty
                        const difficultySelect = document.querySelector(`[name="difficulty_select_${i}"]`);
                        if (difficultySelect) {
                            difficultySelect.value = questionDifficulties[qid].toString();
                            difficultySelect.disabled = true;
                        }
                    }
                }
                
                document.getElementById('question_ids').value = questionIds.join(',');
            }, 500);
        }, 500);
    }
    <?php endif; ?>
});

// --- Sidebar toggle logic (fix for always working) ---
(function() {
    const sidebar = document.getElementById('sidebarColumn');
    const main = document.getElementById('mainColumn');
    const toggleBtn = document.getElementById('toggleSidebar');
    const overlay = document.getElementById('sidebarOverlay');
    let sidebarVisible = false;

    function showSidebar() {
        sidebar.classList.add('visible');
        overlay.classList.add('visible');
        toggleBtn.classList.remove('closed');
        toggleBtn.classList.add('open');
        sidebarVisible = true;
    }

    function hideSidebar() {
        sidebar.classList.remove('visible');
        overlay.classList.remove('visible');
        toggleBtn.classList.remove('open');
        toggleBtn.classList.add('closed');
        sidebarVisible = false;
    }

    // Always start hidden
    hideSidebar();

    // Toggle button click
    toggleBtn.addEventListener('click', function (e) {
        e.stopPropagation();
        if (sidebarVisible) {
            hideSidebar();
        } else {
            showSidebar();
        }
    });

    // Overlay click closes sidebar
    overlay.addEventListener('click', function () {
        hideSidebar();
    });

    // Optional: close sidebar on ESC key
    document.addEventListener('keydown', function(e) {
        if (e.key === "Escape" && sidebarVisible) {
            hideSidebar();
        }
    });
})();
</script>

<?php require_once "include/footer.php"; ?>
