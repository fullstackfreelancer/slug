import Modal from '@typo3/backend/modal.js';

document.addEventListener('click', (event) => {
    const infoButton = event.target.closest('.btn-extension-info');
    
    if (infoButton) {
        event.preventDefault();

        const template = document.getElementById('extension-info-template');
        const modalContent = template ? template.content.cloneNode(true) : 'Content missing';

        Modal.advanced({
            title: 'Extension Information',
            content: modalContent,
            // Try using the string directly if the constant fails
            size: 'large', 
            // Severities are integers: 0 = notice, 1 = info, 2 = ok, 3 = warning, 4 = error
            severity: 1, 
            buttons: [
                {
                    text: 'Close',
                    btnClass: 'btn-default',
                    trigger: () => Modal.dismiss()
                }
            ]
        });
    }
});