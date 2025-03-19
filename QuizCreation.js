document.addEventListener("DOMContentLoaded", () => {
    const quizTypeSelect = document.getElementById("quizType");
    const questionContainer = document.getElementById("questionContainer");
    const addQuestionButton = document.getElementById("addQuestion");
    const submitButton = document.querySelector('button[name="createquizBTN"]');

    let questionCount = 0;
    let selectedQuizType = null;

    const createQuestionTemplate = (index) => {
        const questionWrapper = document.createElement("div");
        questionWrapper.className = "quiz-question-wrapper";
        questionWrapper.id = `question_${index}`;

        // Template for the question
        const template = document.createElement("div");
        template.className = "quiz-question";
        template.dataset.index = index;

        // Basic question structure
        template.innerHTML = `
            <h3>Question ${index + 1}</h3>
            <label>Question:</label>
            <textarea name="quiz_question_${index}" placeholder="Enter your question here" required></textarea>
        `;

        if (selectedQuizType === "MCQ") {
            template.innerHTML += `
            Quiz Answer Selection:<br>
            <div class="answer-group">
                <label><input type="radio" name="correct_answer_${index}" value="Choice_1" required> Correct</label>
                <input type="text" name="question_answer_choice_${index}_1" placeholder="Answer Choice 1" required>
            </div>
            <div class="answer-group">
                <label><input type="radio" name="correct_answer_${index}" value="Choice_2" required> Correct</label>
                <input type="text" name="question_answer_choice_${index}_2" placeholder="Answer Choice 2" required>
            </div>
            <div class="answer-group">
                <label><input type="radio" name="correct_answer_${index}" value="Choice_3" required> Correct</label>
                <input type="text" name="question_answer_choice_${index}_3" placeholder="Answer Choice 3" required>
            </div>
            <div class="answer-group">
                <label><input type="radio" name="correct_answer_${index}" value="Choice_4" required> Correct</label>
                <input type="text" name="question_answer_choice_${index}_4" placeholder="Answer Choice 4" required>
            </div>
            `;
        } else if (selectedQuizType === "T/F") {
            template.innerHTML += `
            Quiz Answer Selection:<br>
            <div class="answer-group">
                <label><input type="radio" name="quiz_answer_${index}" value="True" required> True</label>
                <label><input type="radio" name="quiz_answer_${index}" value="False" required> False</label>
            </div>
            `;
        }        

        template.innerHTML += `
            Answer Explanation:<br>
            <textarea name="answer_explanation_${index}" required></textarea><br>
        `;

        const removeButton = document.createElement("button");
        removeButton.type = "button";
        removeButton.textContent = "Remove Question";
        removeButton.onclick = () => {
            questionWrapper.remove();
            updateQuestionIndices();
        };

        questionWrapper.appendChild(template);
        questionWrapper.appendChild(removeButton);

        return questionWrapper;
    };

    const updateQuestionIndices = () => {
        const questionWrappers = questionContainer.querySelectorAll(".quiz-question-wrapper");
        questionWrappers.forEach((wrapper, index) => {
            wrapper.id = `question_${index}`;
            const question = wrapper.querySelector(".quiz-question");
            question.dataset.index = index;
            question.querySelector("h3").textContent = `Question ${index + 1}`;
            question.querySelectorAll("[name]").forEach((input) => {
                const name = input.name.replace(/\d+/, index);
                input.name = name;
            });
        });
        questionCount = questionWrappers.length;
    };

    quizTypeSelect.addEventListener("change", () => {
        const newType = quizTypeSelect.value;
        if (questionCount > 0 && selectedQuizType && newType !== selectedQuizType) {
            alert("All questions must be of the same type. Remove existing questions to change type.");
            quizTypeSelect.value = selectedQuizType;
            return;
        }
        selectedQuizType = newType;
    });

    addQuestionButton.addEventListener("click", () => {
        if (!selectedQuizType) {
            alert("Please select a quiz type first.");
            return;
        }
        const newQuestion = createQuestionTemplate(questionCount);
        questionContainer.appendChild(newQuestion);
        questionCount++;
    });

    submitButton.addEventListener("click", (event) => {
        if (questionCount < 5) {
            event.preventDefault();
            alert("You must add at least 5 questions.");
        }
    });

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
});
