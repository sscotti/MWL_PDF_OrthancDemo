
<script type="text/javascript">
    // This is intentionaly run after dom loads so this way we can avoid showing duplicate alerts
    // when the user is beeing redirected by persistent table, that happens before this event triggers.
    document.onreadystatechange = function () {
        if (document.readyState == "interactive") {
            Noty.overrideDefaults({
                layout: 'topRight',
                theme: 'backstrap',
                timeout: 2500,
                closeWith: ['click', 'button'],
            });

            // get alerts from the alert bag
            var $alerts_from_php = JSON.parse('<?php echo json_encode(\Alert::getMessages(), 15, 512) ?>');

            // get the alerts from the localstorage
            var $alerts_from_localstorage = JSON.parse(localStorage.getItem('backpack_alerts'))
                ? JSON.parse(localStorage.getItem('backpack_alerts')) : {};

            // merge both php alerts and localstorage alerts
            Object.entries($alerts_from_php).forEach(([type, messages]) => {
                if(typeof $alerts_from_localstorage[type] !== 'undefined') {
                    $alerts_from_localstorage[type].push(...messages);
                } else {
                    $alerts_from_localstorage[type] = messages;
                }
            });

            for (var type in $alerts_from_localstorage) {
                let messages = new Set($alerts_from_localstorage[type]);
                messages.forEach(text => new Noty({type, text}).show());
            }

            // in the end, remove backpack alerts from localStorage
            localStorage.removeItem('backpack_alerts');
        }
    };
</script>
<?php /**PATH /nginx-home/laravel/vendor/backpack/crud/src/resources/views/base/inc/alerts.blade.php ENDPATH**/ ?>