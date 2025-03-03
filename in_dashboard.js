// Encapsulate in an IIFE to avoid polluting the global scope
(function () {
    // Selectors
    const sidebar = document.getElementById('sidebar');
    const menuBar = document.querySelector('#content nav .bx.bx-menu');
    const allSideMenu = document.querySelectorAll('#sidebar .side-menu.top li a');
    const logoImage = document.querySelector('#sidebar .brand img');
    const searchButton = document.querySelector('#content nav form .form-input button');
    const searchButtonIcon = searchButton.querySelector('.bx');
    const searchForm = document.querySelector('#content nav form');

    // Toggle sidebar functionality
    menuBar.addEventListener('click', () => {
        sidebar.classList.toggle('hide');
        if (!sidebar.classList.contains('hide')) {
            logoImage.src = "in_img/logo.png";
            logoImage.classList.add('open-logo');
            logoImage.classList.remove('close-logo');
            logoImage.style.width = "100px";
        } else {
            logoImage.src = "in_img/K.png";
            logoImage.classList.add('close-logo');
            logoImage.classList.remove('open-logo');
            logoImage.style.width = "40px";
        }
    });

    // Handle active menu highlighting
    allSideMenu.forEach(item => {
        item.addEventListener('click', () => {
            allSideMenu.forEach(i => i.parentElement.classList.remove('active'));
            item.parentElement.classList.add('active');
        });
    });

    // Search button toggle
    searchButton.addEventListener('click', (e) => {
        if (window.innerWidth < 576) {
            e.preventDefault();
            searchForm.classList.toggle('show');
            searchButtonIcon.classList.toggle('bx-search', !searchForm.classList.contains('show'));
            searchButtonIcon.classList.toggle('bx-x', searchForm.classList.contains('show'));
        }
    });

    // Quiz selection and performance update
    const quizSelect = document.getElementById('quiz');
    quizSelect?.addEventListener('change', function () {
        const quizId = this.value;
        if (quizId) {
            fetchPerformanceInsights(quizId);
        } else {
            document.querySelector('.performance-insights h3').innerText = '0.00';
        }
    });

    function fetchPerformanceInsights(quizId) {
        fetch('', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `action=get_average&quiz_id=${quizId}`
        })
        .then(response => response.json())
        .then(data => {
            const performanceElement = document.querySelector('.performance-insights h3');
            performanceElement.innerText = (data.average_score || 0).toFixed(2);
        })
        .catch(err => {
            console.error('Error fetching performance insights:', err);
        });
    }

    // Responsive adjustments
    const adjustLayout = () => {
        if (window.innerWidth < 768) {
            sidebar.classList.add('hide');
        } else if (window.innerWidth > 576) {
            searchForm.classList.remove('show');
            searchButtonIcon.classList.replace('bx-x', 'bx-search');
        }
    };

    window.addEventListener('resize', debounce(adjustLayout, 200));
    adjustLayout();

    // Utility: debounce function
    function debounce(func, wait) {
        let timeout;
        return (...args) => {
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(this, args), wait);
        };
    }
})();
