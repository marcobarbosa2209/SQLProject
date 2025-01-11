<?php

function newNotification($message, $type = 'success') {
    $allowedTypes = ['success', 'error'];
    if (!in_array($type, $allowedTypes)) {
        $type = 'success';
    }

    return "
    <style>
        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1050; /* Valor maior para aparecer acima de tudo */
            background-color: #fff;
            border: 1px solid #ddd;
            padding: 15px 20px;
            border-radius: 5px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            font-size: 14px;
            opacity: 0; /* Inicialmente invisível */
            transition: opacity 0.5s ease-in-out; /* Transição para fade-in e fade-out */
        }
        .notification.success {
            border-color: #28a745;
            color: #28a745;
        }
        .notification.error {
            border-color: #dc3545;
            color: #dc3545;
        }
    </style>
    <div class='notification $type' id='notification'>
        <p>$message</p>
    </div>
    <script>
        const notification = document.getElementById('notification');
        if (notification) {
            // Exibe a notificação com fade-in
            notification.style.opacity = '1';
            notification.style.display = 'block';
            
            // Oculta a notificação após 5 segundos com fade-out
            setTimeout(() => {
                notification.style.opacity = '0';
                setTimeout(() => {
                    notification.style.display = 'none';
                }, 500); // Espera o fade-out completar antes de remover
            }, 5000); // Tempo visível (5 segundos)
        }
    </script>";
}
?>