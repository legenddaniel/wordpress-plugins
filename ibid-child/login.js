window.addEventListener('load', function () {

    var form = document.getElementById('register');
    if (!form) return;
    
    var emailConfirm = form.querySelector('.sz-confirm-email-row');
    var passwordConfirm = form.querySelector('.sz-confirm-password-row');

    // Hook customized validation
    form.addEventListener('click', function (e) {
        if (e.target.id !== 'submit-false') return;

        if (this.reg_email.value !== this['confirm-email'].value) {
            return this.scrollIntoView({ behavior: 'smooth' }), emailConfirm.setAttribute('data-msg', 'Emails do not match!');
        }
        if (this.reg_password.value !== this['confirm-password'].value) {
            return this.scrollIntoView({ behavior: 'smooth' }), passwordConfirm.setAttribute('data-msg', 'Passwords do not match!');
        }

        // Trigger the real submission if OK
        this['submit-true'].click();
    })

    // Remove errors when input changes
    form.addEventListener('input', function (e) {
        var id = e.target.id;

        if (['reg_email', 'confirm-email', 'reg_password', 'confirm-password'].indexOf(id) > -1) {
            emailConfirm.dataset.msg && emailConfirm.setAttribute('data-msg', '');
            passwordConfirm.dataset.msg && passwordConfirm.setAttribute('data-msg', '');
        }
    })
})