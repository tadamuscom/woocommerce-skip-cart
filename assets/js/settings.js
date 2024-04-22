const form              			= document.getElementById('smwoo-settings-form');
const skipCart          			= document.getElementById('smwoo-skip-cart');
const clearCart         			= document.getElementById('smwoo-clear-cart');
const completeOrder     			= document.getElementById('smwoo-complete-order');
const nonce             			= document.getElementById('smwoo-settings-nonce');
const action            			= document.getElementById('smwoo-settings-action');
const notificationWrap  			= document.getElementById ('smwoo-notification-wrap');

form.addEventListener('submit', (e) => {
    e.preventDefault()
		
		const skip = !!skipCart.checked;
		const clear = !!clearCart.checked;
		const complete = !!completeOrder.checked;

    notificationWrap.style.display = 'none'
    notificationWrap.classList = ''

    jQuery.ajax({
        type    : "post",
        dataType: 'json',
        url     : smAjax.ajaxurl,
        data    : {
            action  : action.value,
            nonce   : nonce.value,
            skip    : skip,
            clear   : clear,
            complete: complete
        },
        success: function(response) {
            if(response.success){
                notificationWrap.classList.add('smwoo-notification-success')
                notificationWrap.innerText      = response.data.message
                notificationWrap.style.display  = 'inherit'
            }
        }
    })
})
