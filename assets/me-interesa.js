document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.cmi-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            if (btn.disabled) return;
            btn.disabled = true;

            var wrapper    = btn.closest('.cmi-wrapper');
            var countSpan  = wrapper.querySelector('.cmi-contador');
            var textoSpan  = btn.querySelector('.cmi-btn-texto');
            var productId  = btn.getAttribute('data-product-id');
            var nonce      = btn.getAttribute('data-nonce');

            var formData = new FormData();
            formData.append('action', 'cap_me_interesa');
            formData.append('product_id', productId);
            formData.append('nonce', nonce);

            fetch(cmiAjax.url, {
                method: 'POST',
                body: formData
            })
            .then(function (res) { return res.json(); })
            .then(function (data) {
                if (data.success) {
                    countSpan.textContent = data.data.count;
                    if (data.data.texto) {
                        textoSpan.textContent = data.data.texto;
                    }
                    btn.classList.add('cmi-marcado');
                } else {
                    btn.disabled = false;
                }
            })
            .catch(function () { btn.disabled = false; });
        });
    });
});
