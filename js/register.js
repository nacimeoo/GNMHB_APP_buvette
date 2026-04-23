(function () {
    'use strict'

    var forms = document.querySelectorAll('.needs-validation')
    Array.prototype.slice.call(forms)
        .forEach(function (form) {
            form.addEventListener('submit', function (event) {
                if (!form.checkValidity()) {
                    event.preventDefault()
                    event.stopPropagation()
                }
                form.classList.add('was-validated')
            }, false)
        })

    const passwordInput = document.getElementById('password')

    passwordInput.addEventListener('input', function () {
        const val = this.value

        const rules = {
            'rule-length':  val.length >= 8,
            'rule-upper':   /[A-Z]/.test(val),
            'rule-lower':   /[a-z]/.test(val),
            'rule-number':  /[0-9]/.test(val),
            'rule-special': /[\W_]/.test(val),
        }

        for (const [id, ok] of Object.entries(rules)) {
            const el = document.getElementById(id)
            if (ok) {
                el.classList.replace('text-danger', 'text-success')
                el.textContent = '✓ ' + el.textContent.slice(2)
            } else {
                el.classList.replace('text-success', 'text-danger')
                el.textContent = '✗ ' + el.textContent.slice(2)
            }
        }
    })

    document.querySelector('form').addEventListener('submit', function (e) {
        
        const val = document.getElementById('password').value
        const valide =
            val.length >= 8 &&
            /[A-Z]/.test(val) &&
            /[a-z]/.test(val) &&
            /[0-9]/.test(val) &&
            /[\W_]/.test(val)

        if (!valide) {
            passwordInput.setCustomValidity("Le mot de passe ne respecte pas les critères.");
        }else {
            passwordInput.setCustomValidity("");
        }

        if (!form.checkValidity()) {
            e.preventDefault(); 
            e.stopPropagation();
        }

        form.classList.add('was-validated');
    }, false);

})()