(function () {
  'use strict';

  var form = document.getElementById('feedbackForm');
  if (!form) return;

  var phoneRe = /^(\+7|8)\D*\d{3}\D*\d{3}\D*\d{2}\D*\d{2}$/;
  var emailRe = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;

  var TEXT_MAX = 255;
  var AREA_MAX = 4096;

  function showError(fieldName, message) {
    var el = form.querySelector('.error-msg[data-for="' + fieldName + '"]');

    if (el) {
      el.textContent = message;
      el.style.display = 'block';
    }

    var field = form.querySelector('[name="' + fieldName + '"]');
    if (field) field.classList.add('invalid');
  }

  function clearError(fieldName) {
    var el = form.querySelector('.error-msg[data-for="' + fieldName + '"]');

    if (el) {
      el.textContent = '';
      el.style.display = 'none';
    }

    var field = form.querySelector('[name="' + fieldName + '"]');
    if (field) field.classList.remove('invalid');
  }

  function clearAllErrors() {
    form.querySelectorAll('.error-msg').forEach(function (el) {
      el.textContent = '';
      el.style.display = 'none';
    });
    form.querySelectorAll('.invalid').forEach(function (el) {
      el.classList.remove('invalid');
    });
  }

  function showNotification(type, message) {
    var existing = document.getElementById('formNotification');
    if (existing) existing.remove();

    var banner = document.createElement('div');
    banner.id = 'formNotification';
    banner.className = 'form-notification ' + (type === 'success' ? 'notification-success' : 'notification-error');
    banner.innerHTML = '<span class="notification-icon">' + (type === 'success' ? '\u2713' : '\u26a0') + '</span> ' + message;

    form.parentNode.insertBefore(banner, form);

    if (type === 'success') {
      setTimeout(function () {
        banner.classList.add('fade-out');
        setTimeout(function () { banner.remove(); }, 400);
      }, 5000);
    }
  }

  function validateTheme() {
    var val = form.elements['theme'].value;

    if (!val) {
      showError('theme', 'Выберите тему обращения');
      return false;
    }

    clearError('theme');
    return true;
  }

  function validateFullName() {
    var val = form.elements['fullName'].value.trim();

    if (!val) {
      showError('fullName', 'Введите ФИО');
      return false;
    }

    if (val.length > TEXT_MAX) {
      showError('fullName', 'Максимум ' + TEXT_MAX + ' символов');
      return false;
    }

    clearError('fullName');
    return true;
  }

  function validatePhone() {
    var val = form.elements['phone'].value.trim();

    if (!val) {
      showError('phone', 'Введите номер телефона');
      return false;
    }

    if (!phoneRe.test(val)) {
      showError('phone', 'Формат: +7 (999) 000-00-00');
      return false;
    }

    clearError('phone');
    return true;
  }

  function validateEmail() {
    var val = form.elements['email'].value.trim();

    if (!val) {
      showError('email', 'Введите e-mail');
      return false;
    }

    if (!emailRe.test(val)) {
      showError('email', 'Некорректный e-mail');
      return false;
    }

    if (val.length > TEXT_MAX) {
      showError('email', 'Максимум ' + TEXT_MAX + ' символов');
      return false;
    }

    clearError('email');
    return true;
  }

  function validateMessage() {
    var val = form.elements['message'].value.trim();

    if (!val) {
      showError('message', 'Введите сообщение');
      return false;
    }

    if (val.length > AREA_MAX) {
      showError('message', 'Максимум ' + AREA_MAX + ' символов');
      return false;
    }

    clearError('message');
    return true;
  }

  function validateCaptcha() {
    var val = form.elements['captchaAnswer'].value.trim();

    if (!val) {
      showError('captchaAnswer', 'Введите ответ');
      return false;
    }

    clearError('captchaAnswer');
    return true;
  }

  function validateAgreement() {
    if (!form.elements['agreement'].checked) {
      showError('agreement', 'Необходимо согласие на обработку данных');
      return false;
    }

    clearError('agreement');
    return true;
  }

  var messageField = form.elements['message'];
  var counter = document.getElementById('messageCounter');

  function updateCounter() {
    if (!counter) return;

    var len = messageField.value.length;
    counter.textContent = len + ' / ' + AREA_MAX;
    counter.classList.toggle('counter-warn', len > AREA_MAX * 0.9);
  }

  if (messageField && counter) {
    messageField.addEventListener('input', updateCounter);
  }

  form.elements['theme'].addEventListener('change', validateTheme);
  form.elements['fullName'].addEventListener('blur', validateFullName);
  form.elements['phone'].addEventListener('blur', validatePhone);
  form.elements['email'].addEventListener('blur', validateEmail);
  form.elements['message'].addEventListener('blur', validateMessage);
  form.elements['captchaAnswer'].addEventListener('blur', validateCaptcha);
  form.elements['agreement'].addEventListener('change', validateAgreement);

  var captchaQuestionEl = document.getElementById('captchaQuestion');

  function loadCaptcha() {
    fetch('handler.php?action=captcha')
      .then(function (res) { return res.json(); })
      .then(function (data) {
        if (data.question && captchaQuestionEl) {
          captchaQuestionEl.textContent = data.question;
        }
      })
      .catch(function () {
        if (captchaQuestionEl) {
          captchaQuestionEl.textContent = 'Ошибка загрузки капчи';
        }
      });
  }

  loadCaptcha();

  var refreshBtn = document.getElementById('captchaRefresh');

  if (refreshBtn) {
    refreshBtn.addEventListener('click', function () {
      form.elements['captchaAnswer'].value = '';
      clearError('captchaAnswer');
      loadCaptcha();
    });
  }

  form.addEventListener('submit', function (e) {
    e.preventDefault();
    clearAllErrors();

    var clientResults = [
      validateTheme(),
      validateFullName(),
      validatePhone(),
      validateEmail(),
      validateMessage(),
      validateCaptcha(),
      validateAgreement()
    ];

    if (!clientResults.every(function (r) { return r; })) {
      var firstInvalid = form.querySelector('.invalid');

      if (firstInvalid) {
        firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
        firstInvalid.focus();
      }

      return;
    }

    var formData = new FormData(form);

    var submitBtn = form.querySelector('.submit-btn');
    var originalText = submitBtn.textContent;

    submitBtn.disabled = true;
    submitBtn.textContent = 'Отправка...';

    fetch('handler.php', {
      method: 'POST',
      body: formData
    })
      .then(function (res) { return res.json(); })
      .then(function (data) {
        submitBtn.disabled = false;
        submitBtn.textContent = originalText;

        if (data.success) {
          showNotification('success', data.message);
          form.reset();
          updateCounter();
        } else {
          if (data.errors) {
            Object.keys(data.errors).forEach(function (field) {
              showError(field, data.errors[field]);
            });
          }

          showNotification('error', data.message);

          var firstErr = form.querySelector('.invalid');

          if (firstErr) {
            firstErr.scrollIntoView({ behavior: 'smooth', block: 'center' });
            firstErr.focus();
          }
        }

        if (data.captcha && captchaQuestionEl) {
          captchaQuestionEl.textContent = data.captcha;
        }

        form.elements['captchaAnswer'].value = '';
        clearError('captchaAnswer');
      })
      .catch(function () {
        submitBtn.disabled = false;
        submitBtn.textContent = originalText;
        showNotification('error', 'Ошибка соединения с сервером. Попробуйте позже.');
      });
  });
})();
