<!-- Sidebar Toggle Button -->
<style>
    #toggleSidebarBtn {
        position: fixed;
        top: 50%;
        left: 340px; /* Match sidebar width, adjust as needed */
        transform: translateY(-50%);
        z-index: 1050;
        background-color: #ffffff;
        border: 2px solid #0d6efd;
        border-radius: 50%;
        width: 44px;
        height: 44px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: left 0.3s cubic-bezier(.4,2,.6,1), background 0.2s;
        color: #0d6efd;
        font-size: 2rem;
        box-shadow: 0 2px 8px rgba(0,0,0,0.15);
    }

    #toggleSidebarBtn:hover {
        background: #e7f1ff;
        color: #0a58ca;
    }

    #toggleSidebarBtn.closed {
        left: 0;
    }

    #toggleSidebarBtn i {
        font-size: 2rem;
        transition: transform 0.3s cubic-bezier(.4,2,.6,1);
        line-height: 1;
        display: flex;
        align-items: center;
        justify-content: center;
    }
</style>

<button id="toggleSidebarBtn" class="open" type="button" aria-label="Toggle Sidebar">
    <span id="toggleSidebarArrow">&#x25C0;</span>
</button>

<script>
    const toggleBtn = document.getElementById('toggleSidebarBtn');
    const toggleArrow = document.getElementById('toggleSidebarArrow');
    const contentRow = document.getElementById('contentRow');
    const sidebar = document.getElementById('sidebarColumn');
    const main = document.getElementById('mainColumn');
    let sidebarVisible = true;

    function updateToggleBtnPosition() {
        let sidebarWidth = sidebarVisible && sidebar.offsetWidth ? sidebar.offsetWidth : 340;
        if (!sidebarVisible) {
            toggleBtn.classList.add('closed');
            toggleArrow.innerHTML = "&#x25B6;"; // ▶ right arrow
            toggleBtn.style.left = "0";
        } else {
            toggleBtn.classList.remove('closed');
            toggleArrow.innerHTML = "&#x25C0;"; // ◀ left arrow
            toggleBtn.style.left = (sidebarWidth) + "px";
        }
    }

    toggleBtn.addEventListener('click', function () {
        sidebarVisible = !sidebarVisible;
        if (!sidebarVisible) {
            sidebar.style.display = 'none';
            main.classList.remove('col-md-8');
            main.classList.add('col-md-12');
        } else {
            sidebar.style.display = 'block';
            main.classList.remove('col-md-12');
            main.classList.add('col-md-8');
        }
        updateToggleBtnPosition();
    });

    // Initial position
    updateToggleBtnPosition();

    // Responsive: update position on window resize
    window.addEventListener('resize', updateToggleBtnPosition);
</script>
