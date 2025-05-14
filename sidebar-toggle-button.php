<!-- Sidebar Toggle Button -->
<style>
    #toggleSidebar {
        position: fixed;
        top: 38%; /* Slightly above center */
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
<script>
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

hideSidebar();
</script>
