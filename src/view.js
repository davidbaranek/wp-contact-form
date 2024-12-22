/**
 * Use this file for JavaScript code that you want to run in the front-end
 * on posts/pages that contain this block.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-metadata/#view-script
 */

console.log('Hello World! (from create-block-whitepaper block)');

document.addEventListener('DOMContentLoaded', function () {
    const formWrapper = document.querySelector('.wp-block-adm-whitepaper');
    if (formWrapper) {
        attachFormHandler(formWrapper);
    } else {
        const observer = new MutationObserver(() => {
            const block = document.querySelector('.wp-block-adm-whitepaper');
            if (block) {
                observer.disconnect();
                attachFormHandler(block);
            }
        });
        observer.observe(document.body, { childList: true, subtree: true });
    }
});

function attachFormHandler(formWrapper) {
    const requiredFields = formWrapper.querySelectorAll('[required]');
    const formFields = formWrapper.querySelectorAll('input');
    const messageDiv = formWrapper.querySelector('.whitepaper-form-messages');
    const submitButton = formWrapper.querySelector('.whitepaper-submit');

    if (!submitButton) {
        console.error('Submit button not found in the block.');
        return;
    }

    const siteKey = formWrapper.getAttribute('data-sitekey');

    if (!siteKey) {
        console.error(formWrapper);
        console.error('reCAPTCHA site key not entered. Contact administrator.');
        return;
    }

    // Validation on leaving the array
    requiredFields.forEach((field) => {
        field.addEventListener('blur', () => validateField(field));
    });

    // When you click the submit button
    submitButton.addEventListener('click', async function () {
        messageDiv.style.color = '#cc1718';
        messageDiv.textContent = '';

        // Check all required fields
        let isValid = true;
        requiredFields.forEach((field) => {
            validateField(field);
            if (field.classList.contains('invalid')) {
                isValid = false;
            }
        });

        if (!isValid) {
            return;
        }

        submitButton.disabled = true;
        submitButton.innerHTML = `
            <span class="spinner"></span> Sending...
        `;

        // Sběr dat z formuláře
        const formData = {
            first_name: formWrapper.querySelector('.whitepaper-first-name')?.value.trim(),
            last_name: formWrapper.querySelector('.whitepaper-last-name')?.value.trim(),
            email: formWrapper.querySelector('.whitepaper-email')?.value.trim(),
            subscribe: formWrapper.querySelector('.whitepaper-subscribe')?.checked,
            type: formWrapper.querySelector('.whitepaper-type')?.value.trim(),
        };

        try {
            // Get reCaptcha token and pass it to the backend for verification
            const token = await grecaptcha.execute(siteKey, { action: 'submit' });

            if (!token) {
                messageDiv.textContent = 'reCAPTCHA verification failed. Please try again.';
                submitButton.disabled = false;
                submitButton.innerHTML = 'Submit';
                return;
            }
            formData.recaptchaToken = token;

            const response = await fetch(`${window.location.origin}/wp-json/whitepaper/v1/submit/`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(formData),
            });

            const result = await response.json();

            if (!response.ok) {
                // Server returned an error response
                messageDiv.textContent = result.message || 'An error occurred.';
            } else {
                // Success
                messageDiv.style.color = 'green';
                messageDiv.textContent = 'Thank you! Your submission has been sent successfully.';

                // Clear form
                formFields.forEach((field) => {
                    if (field.type === 'checkbox') {
                        field.checked = false;
                    } else {
                        field.value = '';
                    }
                });
            }
        } catch (error) {
            messageDiv.textContent = 'Failed to send. Please try again later.';
        }

        submitButton.disabled = false;
        submitButton.innerHTML = 'Submit';
    });
}

// Function to validate a single field
function validateField(field) {
    const errorHTML = `
        <div class="whitepaper-validation-error" role="alert">
            <p>
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="-2 -2 24 24" width="24" height="24" aria-hidden="true" focusable="false">
                    <path d="M10 2c4.42 0 8 3.58 8 8s-3.58 8-8 8-8-3.58-8-8 3.58-8 8-8zm1.13 9.38l.35-6.46H8.52l.35 6.46h2.26zm-.09 3.36c.24-.23.37-.55.37-.96 0-.42-.12-.74-.36-.97s-.59-.35-1.06-.35-.82.12-1.07.35-.37.55-.37.97c0 .41.13.73.38.96.26.23.61.34 1.06.34s.8-.11 1.05-.34z"></path>
                </svg>
                <span>{ERROR_MESSAGE}</span>
            </p>
        </div>`;

    let errorDiv;

    // Check if the field is a checkbox
    if (field.type === 'checkbox') {
        const label = field.closest('label'); // Locate the closest label
        errorDiv = label?.nextElementSibling;

        // Validation for checkbox
        if (field.required && !field.checked) {
            const errorMessage = 'You must accept the Terms and Conditions.';
            if (!errorDiv || !errorDiv.classList.contains('whitepaper-validation-error')) {
                label.insertAdjacentHTML('afterend', errorHTML.replace('{ERROR_MESSAGE}', errorMessage));
            } else {
                errorDiv.querySelector('span').textContent = errorMessage;
            }
            field.classList.add('invalid');
        } else {
            if (errorDiv && errorDiv.classList.contains('whitepaper-validation-error')) {
                errorDiv.remove();
            }
            field.classList.remove('invalid');
        }
    } else {
        // Validation for other required fields
        errorDiv = field.nextElementSibling;

        if (field.required && !field.value.trim()) {
            const errorMessage = 'This field is required.';
            if (!errorDiv || !errorDiv.classList.contains('whitepaper-validation-error')) {
                field.insertAdjacentHTML('afterend', errorHTML.replace('{ERROR_MESSAGE}', errorMessage));
            } else {
                errorDiv.querySelector('span').textContent = errorMessage;
            }
            field.classList.add('invalid');
        } else if (field.type === 'email' && !isValidEmail(field.value.trim())) {
            const errorMessage = 'Please enter a valid email address.';
            if (!errorDiv || !errorDiv.classList.contains('whitepaper-validation-error')) {
                field.insertAdjacentHTML('afterend', errorHTML.replace('{ERROR_MESSAGE}', errorMessage));
            } else {
                errorDiv.querySelector('span').textContent = errorMessage;
            }
            field.classList.add('invalid');
        } else {
            if (errorDiv && errorDiv.classList.contains('whitepaper-validation-error')) {
                errorDiv.remove();
            }
            field.classList.remove('invalid');
        }
    }
}

// Helper function to validate email format
function isValidEmail(email) {
    const emailRegex = /^[a-zA-Z0-9_.+-]+@[a-zA-Z0-9-]+\.[a-zA-Z0-9-.]+$/;
    return emailRegex.test(email);
}