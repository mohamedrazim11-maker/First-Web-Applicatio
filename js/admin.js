document.addEventListener("DOMContentLoaded", function() {
    const menuToggle = document.getElementById('menu-toggle');
    const sidebar = document.getElementById('sidebar');

    if (menuToggle) {
        menuToggle.addEventListener('click', () => {
            sidebar.classList.toggle('active');
        });
    }

    // Close sidebar when clicking outside on mobile
    document.addEventListener('click', (e) => {
        if (window.innerWidth <= 768 && !sidebar.contains(e.target) && !menuToggle.contains(e.target)) {
            sidebar.classList.remove('active');
        }
    });
});
document.addEventListener("DOMContentLoaded", () => {
    // Add entrance animation to table rows
    const rows = document.querySelectorAll("tr");
    rows.forEach((row, index) => {
        row.style.animation = `fadeInUp 0.5s ease forwards ${index * 0.1}s`;
        row.classList.add("animate__animated");
    });

    // Confirmation for sensitive actions
    const logoutBtn = document.querySelector('a[href="logout.php"]');
    if (logoutBtn) {
        logoutBtn.addEventListener("click", (e) => {
            if (!confirm("Are you sure you want to logout?")) {
                e.preventDefault();
            }
        });
    }
});

// CSS Injection for JS animations
const style = document.createElement('style');
style.innerHTML = `
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
`;
document.head.appendChild(style);
// Add this to your <head> in PHP files: 
// <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

document.addEventListener("DOMContentLoaded", () => {
    const loginForm = document.querySelector('form');
    
    // Add a slight hover tilt effect to the login card
    const card = document.querySelector('.login-container');
    if(card) {
        card.addEventListener('mousemove', (e) => {
            let xAxis = (window.innerWidth / 2 - e.pageX) / 25;
            let yAxis = (window.innerHeight / 2 - e.pageY) / 25;
            card.style.transform = `rotateY(${xAxis}deg) rotateX(${yAxis}deg)`;
        });
    }
});
// Add to your js/admin.js
document.addEventListener('mousemove', (e) => {
    // Subtle background movement based on mouse
    const circles = document.querySelectorAll('.circle');
    const x = e.clientX / window.innerWidth;
    const y = e.clientY / window.innerHeight;
    
    circles.forEach(c => {
        c.style.transform = `translate(${x * 30}px, ${y * 30}px)`;
    });
});

// Interactive button feedback
const btn = document.querySelector('.login-btn');
if(btn) {
    btn.addEventListener('mousedown', () => btn.style.transform = 'scale(0.95)');
    btn.addEventListener('mouseup', () => btn.style.transform = 'scale(1.02)');
}