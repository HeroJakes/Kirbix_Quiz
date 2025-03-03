function editQuiz(quizId) {
    window.location.href = `edit_quiz.php?quiz_id=${quizId}`;
}

function deleteQuiz(quizId) {
    if (confirm(`Are you sure you want to delete quiz ID: ${quizId}?`)) {
        alert(`Deleting quiz with ID: ${quizId}`);
        window.location.href = `delete_quiz.php?quiz_id=${quizId}`
    }
}

const body = document.querySelector("body"),
    sidebar = body.querySelector("nav"),
    toggle = body.querySelector(".toggle"),
    searchBtn = body.querySelector(".search-box"),
    modeSwitch = body.querySelector(".toggle-switch"),
    modeText = body.querySelector(".mode-text");
logoImage = document.querySelector(".sidebar header .image img");

// Sidebar toggle functionality
toggle.addEventListener("click", () => {
    sidebar.classList.toggle("close");
    if (!sidebar.classList.contains("close")) {
        logoImage.src = "images/logo.png";
        logoImage.classList.add("open-logo");
        logoImage.classList.remove("close-logo");
    } else {
        logoImage.src = "images/K.png";
        logoImage.classList.add("close-logo");
        logoImage.classList.remove("open-logo");
    }
});

// Search button functionality
searchBtn.addEventListener("click", () => {
    sidebar.classList.remove("close");
});

// Dark mode toggle functionality
modeSwitch.addEventListener("click", () => {
    body.classList.toggle("dark");
    if (body.classList.contains("dark")) {
        modeText.innerText = "Light mode";
    } else {
        modeText.innerText = "Dark mode";
    }
});

// Starting the quiz (from dynamic quiz card content)
document.getElementById("startbtn3").addEventListener("click", function () {
    document.querySelector(".home").innerHTML = `
        <div class="quiz-start">
        <div class = "container">
            <div class="quiz-start-cfm">
                <div class="wrap">
                    <img src="images/quiz3.png" alt="Image of Quiz">
                </div>
                <h1>Title</h1>
                <p>Instructor Name</p>
                <h2>Category</h2>
                <h2>Chapter</h2>
                <h2>Difficulty</h2>
                <p>Description</p>
            </div>
        </div>
        <div class="startquizbtn">
            <button onclick="attemptQuiz()">Attempt Quiz</button>
            <button onclick="cancelQuiz()">Cancel</button>
        </div>
    </div>`;
    // Scroll to top after content change
    document.querySelector('.home').scrollTop = 0;
});

// Handling the "See More" button functionality for listing quizzes
document.getElementById("seeMoreButton").addEventListener("click", function () {
    document.querySelector(".home").innerHTML = `
        <div class="quiz-sec">
        <h3>Let's Get Started!</h3>
        <p>Description</p>
        <div>
            <button>Form 4</button>
            <button>Form 5</button>
        </div>
        <div class="quiz-list">
            <div class="quiz-card">
                <img src="images/quiz1.png" alt="Image of Quiz 1">
                <h4>Title</h4>
                <p>Instructor Name</p>
                <button>Start Quiz</button>
            </div>
            <div class="quiz-card">
                <img src="images/quiz2.png" alt="Image of Quiz 2">
                <h4>Title</h4>
                <p>Instructor Name</p>
                <button>Start Quiz</button>
            </div>
            <div class="quiz-card">
                <img src="images/quiz3.png" alt="Image of Quiz 3">
                <h4>Title</h4>
                <p>Instructor Name</p>
                <button>Start Quiz</button>
            </div>
            <div class="quiz-card">
                <img src="images/quiz1.png" alt="Image of Quiz 1">
                <h4>Title</h4>
                <p>Instructor Name</p>
                <button>Start Quiz</button>
            </div>
            <div class="quiz-card">
                <img src="images/quiz2.png" alt="Image of Quiz 2">
                <h4>Title</h4>
                <p>Instructor Name</p>
                <button>Start Quiz</button>
            </div>
            <div class="quiz-card">
                <img src="images/quiz3.png" alt="Image of Quiz 3">
                <h4>Title</h4>
                <p>Instructor Name</p>
                <button>Start Quiz</button>
            </div>
        </div>
    </div>`;
    document.querySelector('.home').scrollTop = 0;


})
