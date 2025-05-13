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
                $sql = "SELECT qu_id, text FROM questions WHERE ca_id = ?";
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
                $sql .= " AND is_active = 1 GROUP BY text ORDER BY RAND() LIMIT 5"; // <-- Add GROUP BY text to remove duplicates
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
}
.hamburger-bar {
    width: 22px;
    height: 3px;
    background: #0d6efd;
    margin: 2.5px 0;
    border-radius: 2px;
    transition: all 0.25s;
}
#toggleSidebar.open .hamburger-bar:nth-child(1) {
    transform: translateY(5.5px) rotate(45deg);
}
#toggleSidebar.open .hamburger-bar:nth-child(2) {
    opacity: 0;
}
#toggleSidebar.open .hamburger-bar:nth-child(3) {
    transform: translateY(-5.5px) rotate(-45deg);
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

            // --- SEARCH UI ---
            const controlsDiv = document.createElement('div');
            controlsDiv.style = "display:flex;gap:10px;align-items:center;margin-top:5px;position:relative;";

            const searchInput = document.createElement('input');
            searchInput.type = 'text';
            searchInput.placeholder = 'Search questions...';
            searchInput.className = 'form-control form-control-sm';
            searchInput.style = "max-width:200px;";

            const searchResults = document.createElement('div');
            searchResults.className = 'search-results';
            searchResults.style = "position:absolute;top:100%;left:0;background:white;border:1px solid #ddd;max-height:200px;overflow-y:auto;display:none;z-index:1000;width:300px;box-shadow:0 2px 4px rgba(0,0,0,0.1);margin-top:2px;border-radius:4px;";

            function loadQuestions(searchTerm = '') {
                const courseId = document.getElementById('course_id').value;
                const categoryId = document.getElementById('category_id').value;
                const difficulty = difficultySelect.value;
                const url = `generate-test.php?action=get_random_question&course_id=${courseId}&category_id=${categoryId}&difficulty=${difficulty}&search=${encodeURIComponent(searchTerm)}&list=1`;
                fetch(url)
                    .then(res => res.json())
                    .then(data => {
                        searchResults.innerHTML = '';
                        if (data.questions && data.questions.length > 0) {
                            searchResults.style.display = 'block';
                            data.questions.forEach(q => {
                                const div = document.createElement('div');
                                div.style = "padding:8px 12px;cursor:pointer;border-bottom:1px solid #eee;";
                                div.onmouseover = () => div.style.backgroundColor = '#f5f5f5';
                                div.onmouseout = () => div.style.backgroundColor = '';
                                div.innerHTML = `<em>${q.text}</em>`;
                                div.onclick = () => {
                                    questionData[i] = q.qu_id;
                                    preview.innerHTML = `<em>${q.text}</em>`;
                                    document.getElementById('question_ids').value = questionData.filter(id => id).join(',');
                                    searchResults.style.display = 'none';
                                    searchInput.value = '';
                                };
                                searchResults.appendChild(div);
                            });
                        } else {
                            searchResults.style.display = 'none';
                        }
                    });
            }
            // Load 10 questions by default
            loadQuestions();

            let debounceTimer;
            searchInput.addEventListener('input', function() {
                clearTimeout(debounceTimer);
                const searchTerm = this.value.trim();
                debounceTimer = setTimeout(() => loadQuestions(searchTerm), 300);
            });

            // Hide results when clicking outside
            document.addEventListener('click', function(e) {
                if (!searchResults.contains(e.target) && e.target !== searchInput) {
                    searchResults.style.display = 'none';
                }
            });
            searchInput.addEventListener('focus', () => {
                if (searchResults.children.length > 0) {
                    searchResults.style.display = 'block';
                }
            });
            // --- END SEARCH UI ---

            const rerollBtn = document.createElement('button');
            rerollBtn.type = 'button';
            rerollBtn.innerText = '🔄 Reroll';
            rerollBtn.style = "margin-top:5px;";
            rerollBtn.onclick = () => fetchQuestion(i);

            controlsDiv.appendChild(searchInput);
            controlsDiv.appendChild(searchResults);
            controlsDiv.appendChild(rerollBtn);

            wrapper.innerHTML = `<strong>Question ${i + 1}</strong><br>`;
            wrapper.appendChild(document.createTextNode("Difficulty: "));
            wrapper.appendChild(difficultySelect);
            wrapper.appendChild(document.createElement('br'));
            wrapper.appendChild(preview);
            wrapper.appendChild(controlsDiv);
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
}

function hideSidebar() {
    sidebar.classList.remove('visible');
    overlay.classList.remove('visible');
    toggleBtn.classList.remove('open');
    toggleBtn.classList.add('closed');
}

toggleBtn.addEventListener('click', function (e) {
    e.stopPropagation();
    sidebarVisible = !sidebarVisible;
    if (sidebarVisible) {
        showSidebar();
    } else {
        hideSidebar();
    }
});

overlay.addEventListener('click', function () {
    sidebarVisible = false;
    hideSidebar();
});

// Ensure sidebar is hidden on load
hideSidebar();
</script>

<?php require_once "include/footer.php"; ?>