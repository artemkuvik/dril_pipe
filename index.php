<?php session_start(); ?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Dril Pipe Service</title>
	<link rel="stylesheet" type="text/css" href="src/css/index.css">
</head>
<body>
    <div class="btn_contact">
        <a href="?openModal=true" class="btn_contact"><img src="src/icon/contact.svg"></a>
    </div>  
	<div class="top_content">
		<?php require 'header.php'; ?>
		<div class="start_block">
			<div class="text_info">
				<div class="display_text">
					<div class="title_text"><span>Dril Pipe Service</span></div>
					<div class="subtitle_text"><span>Занимаемся изготовлением элементов трубных колонн и реализацией трубной продукции под различные нужды.</span></div>
				</div>
			</div>
			<div class="gallery">
				<img src="src/image/centr_photo.png">
			</div>
		</div>
	</div>
	<div class="main_content">
		<div class="service">
			<div class="title_block_service">
				<span>Наши услуги</span>
			</div>
			<div class="arrow_nav">
				<img class="arrow_left" src="src/icon/arrow_left.svg">
				<img class="arrow_right" src="src/icon/arrow_right.svg">
			</div>
			<div class="slider_card_service">
                <script>
document.addEventListener('DOMContentLoaded', function () {
    const sliderContainer = document.querySelector('.slider_card_service');
    let servicesData = [];
    let currentIndex = 0;
    const itemsPerSlide = 3;
    const userId = <?php echo isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'null'; ?>;

    function loadServices() {
        fetch('php_scripts/query_info_services.php')
            .then(response => response.json())
            .then(data => {
                servicesData = data;
                renderServices();
            })
            .catch(error => console.error('Error loading services:', error));
    }

    function renderServices() {
        sliderContainer.innerHTML = '';
        const visibleServices = servicesData.slice(currentIndex, currentIndex + itemsPerSlide);
        
        visibleServices.forEach(service => {
            const cardHTML = `
                <div class="card_service">
                    <div class="image_service">
                        <img src="${service.image}" alt="${service.title}">
                    </div>
                    <div class="info_service">
                        <div class="name_service">
                            <span>${service.title}</span>
                        </div>
                        <div class="cost_service">
                            <span>${service.cost} Руб</span>
                        </div>
                        <div class="desc_service">
                            <span>${service.description}</span>
                        </div>
                        <div class="btn_service">
                            <a href="#" class="request_service_btn" data-service-id="${service.id}">Оставить заявку</a>
                        </div>
                    </div>
                </div>
            `;
            sliderContainer.innerHTML += cardHTML;
        });

        document.querySelectorAll('.request_service_btn').forEach(button => {
            button.addEventListener('click', openRequestModal);
        });
    }

    function openRequestModal(event) {
        event.preventDefault();
        const serviceId = event.currentTarget.getAttribute('data-service-id');

        if (userId === null) {
            window.location.href = "login_and_register_page.php";
        } else {
            document.getElementById('userIdInput').value = userId;
            document.getElementById('serviceIdInput').value = serviceId;
            document.getElementById('serviceRequestModal').style.display = 'block';
        }
    }

    function closeRequestModal() {
        document.getElementById('serviceRequestModal').style.display = 'none';
    }

    document.querySelector('.close_modal_custom').addEventListener('click', closeRequestModal);
    window.addEventListener('click', function(event) {
        if (event.target === document.getElementById('serviceRequestModal')) {
            closeRequestModal();
        }
    });

    function nextSlide() {
        if (currentIndex + itemsPerSlide < servicesData.length) {
            currentIndex += itemsPerSlide;
            renderServices();
        }
    }

    function prevSlide() {
        if (currentIndex > 0) {
            currentIndex -= itemsPerSlide;
            renderServices();
        }
    }

    document.querySelector('.arrow_left').addEventListener('click', prevSlide);
    document.querySelector('.arrow_right').addEventListener('click', nextSlide);

    loadServices();
});

                </script>
			</div>
		</div>

<!-- Модальное окно для заявки -->
<div class="modal_custom" id="serviceRequestModal">
    <div class="modal_custom_content">
        <span class="close_modal_custom">&times;</span>
        <form action="php_scripts/service_app.php" method="POST">
            <input type="hidden" id="userIdInput" name="user_id">
            <input type="hidden" id="serviceIdInput" name="service_id">
            
            <label for="issue">Если у вас есть вопросы по услуге (необязательно)</label><br>
            <textarea id="issue" name="issue"></textarea><br><br>
            
            <button type="submit">Оставить заявку</button>
        </form>
    </div>
</div>








<?php 
require 'footer.php'; ?>

<!-- Модальное окно -->
<div id="contactModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <span class="title_form">Если у вас есть вопросы, оставьте сообщение</span>
        <form id="contactForm">
            <textarea name="message" placeholder="Ваше сообщение" required></textarea>
            <button type="submit">Отправить</button>
        </form>
    </div>
</div>

<script>
 // Получить модальное окно
var modal = document.getElementById("contactModal");

// Получить элемент <span>, который закрывает модальное окно
var span = document.getElementsByClassName("close")[0];

// Проверка URL на наличие параметра 'openModal'
var urlParams = new URLSearchParams(window.location.search);
if (urlParams.get('openModal') === 'true') {
    modal.style.display = "block"; // Если параметр есть, открываем модальное окно
}

// Закрытие модального окна при клике на <span> (x)
span.onclick = function() {
    modal.style.display = "none";
    // Обновить URL без параметра openModal
    history.pushState(null, null, window.location.pathname); // Убираем параметр из URL
}

// Закрытие модального окна при клике за его пределы
window.onclick = function(event) {
    if (event.target == modal) {
        modal.style.display = "none";
        // Обновить URL без параметра openModal
        history.pushState(null, null, window.location.pathname); // Убираем параметр из URL
    }
}

// Отправка формы через AJAX
document.getElementById("contactForm").onsubmit = function(event) {
    event.preventDefault(); // Предотвратить стандартное поведение формы

    // Собираем данные формы
    var formData = new FormData(this);

    // Отправляем данные на сервер
    fetch('php_scripts/send_contact.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(data => {
        alert(data); // Вывести сообщение о результате
        modal.style.display = "none"; // Закрыть модальное окно
        document.getElementById("contactForm").reset(); // Очистить форму
        // Обновить URL без параметра openModal
        history.pushState(null, null, window.location.pathname); // Убираем параметр из URL
    })
    .catch(error => console.error('Ошибка:', error));
};

</script>



</body>
</html>
