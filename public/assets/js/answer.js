document.addEventListener('DOMContentLoaded', function() {

    // --------------------------------------------------------------------------------------------------------
    // ABOVE TABLE


    // ----------------------------- Handle for "Download Department Head Verification Form" Modal -----------------------------
        var downloadBtn = document.getElementById('downloadHdVerificationBtn');
        var noForReviewModal = document.getElementById('noForReviewModal');
        var closeNoForReviewModal = document.getElementById('closeNoForReviewModal');

        if (downloadBtn && downloadBtn.getAttribute('data-hasforreview') === "0") {
            downloadBtn.addEventListener('click', function(e) {
                e.preventDefault();
                if (noForReviewModal) {
                    noForReviewModal.classList.remove('hidden');
                }
            });
        }

        if (closeNoForReviewModal) {
            closeNoForReviewModal.addEventListener('click', function() {
                noForReviewModal.classList.add('hidden');
            });
        }
    // ----------------------------- Handle for "Download Department Head Verification Form" Modal -----------------------------


    // ----------------------------- Handle for "Upload Department Head Verification Form" Modal -----------------------------
        const openDhVerificationModalBtn = document.getElementById('openDhVerificationModalBtn');
        const uploadDhVerificationModal = document.getElementById('uploadDhVerificationModal');
        const uploadNotAllowedModal = document.getElementById('uploadNotAllowedModal');
        const closeUploadNotAllowedModal = document.getElementById('closeUploadNotAllowedModal');
        const cancelDhVerificationUpload = document.getElementById('cancelDhVerificationUpload');
        const closeDhVerificationUpload = document.getElementById('closeDhVerificationUpload');
        const dhVerificationUploadForm = document.getElementById('dhVerificationUploadForm');
        const dhVerificationConfirmInput = document.getElementById('dhVerificationConfirmInput');
        const confirmError = document.getElementById('confirmError');
        const fileInput = document.getElementById('dhVerificationFile');
        const fileError = document.getElementById('fileError');
        const decisionError = document.getElementById('decisionError');

        if (openDhVerificationModalBtn) {
            if (openDhVerificationModalBtn.getAttribute('data-hasforreview') === "0") {
                openDhVerificationModalBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    if (uploadNotAllowedModal) {
                        uploadNotAllowedModal.classList.remove('hidden');
                    }
                });
            } else if (uploadDhVerificationModal) {
                openDhVerificationModalBtn.addEventListener('click', function() {
                    uploadDhVerificationModal.classList.remove('hidden');
                });
            }
        }
        
        if (closeUploadNotAllowedModal) {
            closeUploadNotAllowedModal.addEventListener('click', function() {
                uploadNotAllowedModal.classList.add('hidden');
            });
        }

        if (openDhVerificationModalBtn && uploadDhVerificationModal) {
            openDhVerificationModalBtn.addEventListener('click', function() {
                uploadDhVerificationModal.classList.remove('hidden');
            });
        }
        if (cancelDhVerificationUpload && uploadDhVerificationModal) {
            cancelDhVerificationUpload.addEventListener('click', function() {
                uploadDhVerificationModal.classList.add('hidden');
            });
        }
        // if (closeDhVerificationUpload && uploadDhVerificationModal) {
            closeDhVerificationUpload.addEventListener('click', function() {
                console.log("sample")
                uploadDhVerificationModal.classList.add('hidden');
            });
        // }

        // Prevent copy-paste in confirm input
        if (dhVerificationConfirmInput) {
            dhVerificationConfirmInput.addEventListener('paste', function(e) {
                e.preventDefault();
            });
        }

        if (dhVerificationUploadForm) {
            dhVerificationUploadForm.addEventListener('submit', function(e) {
                let valid = true;

                // 1. File must be PDF and required
                fileError.classList.add('hidden');
                if (!fileInput.files.length || fileInput.files[0].type !== 'application/pdf') {
                    fileError.classList.remove('hidden');
                    valid = false;
                }

                // 2. All decisions must be selected
                decisionError.classList.add('hidden');
                const answerGroups = dhVerificationUploadForm.querySelectorAll('[data-answer-id]');
                for (let group of answerGroups) {
                    const answerId = group.getAttribute('data-answer-id');
                    const radios = dhVerificationUploadForm.querySelectorAll(`input[name="decision[${answerId}]"]`);
                    let checked = false;
                    for (let radio of radios) {
                        if (radio.checked) checked = true;
                    }
                    if (!checked) {
                        decisionError.classList.remove('hidden');
                        valid = false;
                        break;
                    }
                }

                // 3. Confirm input must be typed as "confirm" (no copy-paste)
                confirmError.classList.add('hidden');
                if (dhVerificationConfirmInput.value.trim() !== 'confirm') {
                    confirmError.classList.remove('hidden');
                    valid = false;
                }

                if (!valid) {
                    e.preventDefault();
                    return false;
                }

                // If all validations pass, allow AJAX submit as before
                e.preventDefault();
                const formData = new FormData(dhVerificationUploadForm);

                fetch('/questionnaire/upload-dh-verification', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        uploadDhVerificationModal.classList.add('hidden');
                        // location.reload();

                        // Show success toast
                        showToast('Response submitted successfully!', 'success');
                    } else {
                        showToast('Upload failed.', 'error');
                        // alert(data.message || 'Upload failed.');
                    }

                    // After successful submission, reload the page after a short delay
                    setTimeout(function() {
                        location.reload();
                    }, 2000);
                })
                .catch(() => {
                    // alert('An error occurred while uploading.');
                    showToast('An error occurred while uploading.', 'error');
                });
            });

            dhVerificationConfirmInput.addEventListener('input', function() {
                if (dhVerificationConfirmInput.value.trim() === 'confirm') {
                    confirmError.classList.add('hidden');
                }
            });
        }
    // ----------------------------- Handle for "Upload Department Head Verification Form" Modal -----------------------------


    // ----------------------------- Color Toggle -----------------------------
        const colorToggle = document.getElementById('colorToggle');
        const tableWrapper = document.querySelector('.overflow-x-auto');

        // Utility function to apply Tailwind classes based on status
        function applyRowColors(enable) {
            document.querySelectorAll('.question-row').forEach(function(row) {
                // Always clear any previous background-color style
                row.style.backgroundColor = '';
                if (enable) {
                    const status = row.getAttribute('data-status');
                    if (status === 'pending') {
                        row.style.backgroundColor = '#fff4e6'; // yellow-400
                    } else if (status === 'accepted') {
                        row.style.backgroundColor = '#b2f2bb'; // green-300
                    } else if (status === 'rejected' || status === 'returned') {
                        row.style.backgroundColor = '#ffc9c9'; // red-300
                    } else if (status === 'for review') {
                        row.style.backgroundColor = '#a5d8ff'; // blue-300
                    } else if (status === 'no answer' || status ===  'draft' ) {
                        row.style.backgroundColor = '#ffffff'; // white
                    }
                }
            });
        }
        

        // Restore toggle state from localStorage on page load
        const savedToggle = localStorage.getItem('colorToggleEnabled');
        if (savedToggle === 'true') {
            colorToggle.checked = true;
            applyRowColors(true);
        } else {
            colorToggle.checked = false;
            applyRowColors(false);
        }

        colorToggle.addEventListener('change', function() {
            applyRowColors(this.checked);
            localStorage.setItem('colorToggleEnabled', this.checked ? 'true' : 'false');
        });
    // ----------------------------- Color Toggle -----------------------------

    // ----------------------------- Status Filter Dropdown -----------------------------

    const statusFilter = document.getElementById('statusFilter');
    console.log(statusFilter)
    // Function to filter rows by status
    function filterRowsByStatus(selectedStatus) {
        document.querySelectorAll('.question-row').forEach(function(row) {
            const rowStatus = row.getAttribute('data-status');
            
            if (selectedStatus === 'all') {
                row.classList.remove('hidden');
            } else if (selectedStatus === 'rejected') {
                // Special case for "Rejected/Returned"
                if (rowStatus === 'rejected' || rowStatus === 'returned') {
                    row.classList.remove('hidden');
                } else {
                    row.classList.add('hidden');
                }
            } else if (selectedStatus === 'no answer') {
                // Special case for "No Answer/Draft"
                if (rowStatus === 'no answer' || rowStatus === 'draft') {
                    row.classList.remove('hidden');
                } else {
                    row.classList.add('hidden');
                }
            } else {
                // Normal case
                if (rowStatus === selectedStatus) {
                    row.classList.remove('hidden');
                } else {
                    row.classList.add('hidden');
                }
            }
        });
    }
    
    // Restore filter state from localStorage on page load
    const savedFilter = localStorage.getItem('statusFilterValue');
    if (savedFilter) {
        statusFilter.value = savedFilter;
        filterRowsByStatus(savedFilter);
    }
    
    // Handle filter change
    statusFilter.addEventListener('change', function() {
        const selectedStatus = this.value;
        filterRowsByStatus(selectedStatus);
        localStorage.setItem('statusFilterValue', selectedStatus);
    });

    // console.log('')

// ----------------------------- Status Filter Dropdown -----------------------------

    // ----------------------------- Info Button Toggle  -----------------------------
        const infoBtn = document.getElementById('infoBtn');
        const infoTooltip = document.getElementById('infoTooltip');

        infoBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            infoTooltip.classList.toggle('hidden');
        });

        // Hide tooltip when clicking outside
        document.addEventListener('click', function(e) {
            if (!infoTooltip.classList.contains('hidden')) {
                infoTooltip.classList.add('hidden');
            }
        });

        // Prevent tooltip from closing when clicking inside
        infoTooltip.addEventListener('click', function(e) {
            e.stopPropagation();
        });
    // ----------------------------- Info Button Toggle  -----------------------------
    // --------------------------------------------------------------------------------------------------------



    // --------------------------------------------------------------------------------------------------------
    // FORMS
    // ----------------------------- Enable file upload after choosing an answer from Yes or No -----------------------------
        $('.answer-radio').change(function () {
            currentQuestionId = $(this).data('question-id');
            currentQuestionnaireId = $(this).data('questionnaire-id');
            const $fileInput = $(`input[name="files_${currentQuestionId}[]"]`);
            $fileInput.prop('disabled', false);
        });
    // ----------------------------- Enable file upload after choosing an answer from Yes or No -----------------------------


    // ----------------------------- Handle clear button for draft files -----------------------------
        const clearDraftFilesState = {};

        // Handle "Clear files" button click
        $(document).on('click', '.clear-draft-files-btn', function () {
            const questionId = $(this).data('question-id');
            // Remove the displayed uploaded files
            $(`#draft-files-list-${questionId}`).remove();
            $(this).remove();
            // Set the hidden input value to 1 (true)
            $(`input[name="clear_${questionId}"]`).val('1');
            clearDraftFilesState[questionId] = true;
        });
        
    // ----------------------------- Handle clear button for draft files -----------------------------

    // ----------------------------- Handle clear button for files -----------------------------

    $(document).on('click', '.clear-files-btn', function () {
        // console.log("Hello")
        const questionId = $(this).data('question-id');
        // Clear the file input
        $(`input[name="files_${questionId}[]"]`).val('');
        // Remove the file preview
        $(`input[name="files_${questionId}[]"]`).siblings('.selected-files-preview').empty();
        // Set the hidden clear input to 1 (true)
        $(`input[name="clear_${questionId}"]`).val('1');
        // Hide the clear button
        $(this).addClass('hidden');
    });

    // ----------------------------- Handle clear button for files -----------------------------


    // ----------------------------- Handle file selection and preview/display -----------------------------
        // Helper function to escape HTML
        function escapeHtml(text) {
            if (!text) return '';
            return String(text)
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");
        }

        $('.file-input').on('change', function() {
            const MAX_TOTAL_SIZE_MB = 10; // Set your group limit here (in MB)
            const MAX_TOTAL_SIZE = MAX_TOTAL_SIZE_MB * 1024 * 1024; // in bytes
            const MAX_FILES = 5; // Maximum number of files allowed
            const files = this.files;
            let valid = true;
            let errorMsg = "";
            let totalSize = 0;

            // Check file count limit
            if (files.length > MAX_FILES) {
                valid = false;
                errorMsg = `You can only upload a maximum of ${MAX_FILES} files.`;
            }

            for (let i = 0; i < files.length; i++) {
                totalSize += files[i].size;
            }

            if (totalSize > MAX_TOTAL_SIZE) {
                valid = false;
                errorMsg = `The total size of selected files must be less than ${MAX_TOTAL_SIZE_MB} MB.`;
            }

            if (!valid) {
                // Clear the input and preview
                $(this).val('');
                $(this).siblings('.selected-files-preview').empty();
                // Optionally show an error message (customize as needed)
                alert(errorMsg);
                // showToast(errorMsg, 'error');
                // Hide the clear button if present
                const questionId = $(this).attr('name').match(/\d+/)[0];
                $(`.clear-files-btn[data-question-id="${questionId}"]`).addClass('hidden');
                return;
            }

            const previewContainer = $(this).siblings('.selected-files-preview');
            previewContainer.empty();
            
            // display through console.log the files.length
            // console.log("files.length:", files.length)
            // console.log("File input changed");

            if (files.length > 0) {
                const fileList = $('<div class="space-y-2"></div>');
                
                for (let i = 0; i < files.length; i++) {
                    const file = files[i];
                    const fileSize = (file.size / 1024).toFixed(2) + ' KB';
                    const fileExtension = file.name.split('.').pop().toLowerCase();
                    
                    const fileItem = $(`
                        <div class="flex items-center justify-between p-2 bg-gray-100 rounded">
                            <div class="flex items-center">
                                <i class="fa ${fileExtension === 'pdf' ? 'fa-file-pdf-o' : 'fa-file-excel-o'} text-primary mr-2"></i>
                                <div>
                                    <div class="font-medium text-sm truncate max-w-xs">${escapeHtml(file.name)}</div>
                                    <div class="text-xs text-gray-500">${fileSize}</div>
                                </div>
                            </div>
                        </div>
                    `);
                    
                    fileList.append(fileItem);
                }
                
                previewContainer.append(fileList);
            }

            const questionId = $(this).attr('name').match(/\d+/)[0];
            $(`input[name="clear_${questionId}"]`).val('0');
            clearDraftFilesState[questionId] = false;

            console.log("clearDraftFilesState[questionId]:", clearDraftFilesState[questionId])

            // // Show the clear button if files are selected
            // if (this.files.length > 0) {
            //     $(`.clear-files-btn[data-question-id="${questionId}"]`).removeClass('hidden');
            //     // Display the class of .clear-files-btn
            //     if ($('.clear-files-btn[data-question-id="123"]').length > 0) {
            //         // The clear button for question 123 exists
            //     }
            //     console.log("Greater than 0, clearFilesBtn class:", $(`.clear-files-btn[data-question-id="${questionId}"]`).attr('class'));
            // } else {
            //     $(`.clear-files-btn[data-question-id="${questionId}"]`).addClass('hidden');

            //     if ($('.clear-files-btn[data-question-id="123"]').length > 0) {
            //         // The clear button for question 123 exists
            //     }

            //     // Display the class of.clearFilesBtn
            //     console.log("Not Greater than 0, clearFilesBtn class:", $(`.clear-files-btn[data-question-id="${questionId}"]`).attr('class'));
            // }
        });
    // ----------------------------- Handle file selection and preview/display -----------------------------
    


    



    // ----------------------------- Toaster -----------------------------
        const toasterContainer = document.getElementById('toaster-container');

        // Create toaster container if it doesn't exist
        if (!toasterContainer) {
            const newToasterContainer = document.createElement('div');
            newToasterContainer.id = 'toaster-container';
            newToasterContainer.className = 'fixed bottom-4 right-4 z-50 flex flex-col items-end';
            document.body.appendChild(newToasterContainer);
        }

        // Toaster notification system
        function showToast(message, type = 'success') {

            // console.log("showToast reached")
            // console.log("showToast message:", message)
            // console.log("showToast type:", type)
            // Get or create toaster container
            let toasterContainer = document.getElementById('toaster-container');
            if (!toasterContainer) {
                toasterContainer = document.createElement('div');
                toasterContainer.id = 'toaster-container';
                toasterContainer.className = 'fixed top-4 right-4 z-50 flex flex-col items-end';
                document.body.appendChild(toasterContainer);
            }
            
            // Create toast element
            const toast = document.createElement('div');
            toast.className = `flex items-center p-4 mb-3 rounded-lg shadow-lg transition-all transform translate-x-full ${
                type === 'success' ? 'bg-green-100 border-l-4 border-green-500 text-green-700' : 
                'bg-red-100 border-l-4 border-red-500 text-red-700'
            }`;

            // Add icon based on type
            const iconClass = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';
            
            toast.innerHTML = `
                <div class="flex items-center">
                    <div class="mr-3">
                        <i class="fa ${iconClass} fa-2x"></i>
                    </div>
                    <div>
                        <p class="font-medium">${type === 'success' ? 'Success' : 'Error'}</p>
                        <p class="text-sm">${message}</p>
                    </div>
                </div>
                <button class="ml-2 text-gray-400 hover:text-gray-500">
                    <i class="fa fa-times"></i>
                </button>
            `;
            
            // Add to container
            toasterContainer.appendChild(toast);
            
            // Animate in
            setTimeout(() => {
                toast.classList.remove('translate-x-full');
            }, 10);
            
            // Add close button functionality
            const closeButton = toast.querySelector('button');
            closeButton.addEventListener('click', () => {
                removeToast(toast);
            });
            
            // Auto remove after 5 seconds
            setTimeout(() => {
                removeToast(toast);
            }, 5000);
        }

        function removeToast(toast) {
            // Animate out
            toast.classList.add('translate-x-full');
            
            // Remove from DOM after animation
            setTimeout(() => {
                toast.remove();
            }, 300);
        }
    // ----------------------------- Toaster -----------------------------
    // --------------------------------------------------------------------------------------------------------
    
    
    
    // --------------------------------------------------------------------------------------------------------
    // TOGGLE DROPDOWN
    // ----------------------------- Attach toggleDropdown to view submission buttons -----------------------------
        function toggleDropdown(id) {
            const dropdown = $(`#${id}`);
            dropdown.toggleClass('hidden');

            const button = $(`#view-submission-${id.split('-')[1]}`);
            if (dropdown.hasClass('hidden')) {
                button.text("View Submission");
            } else {
                button.text("Hide Submission");
            }
        }

        $('[id^="view-submission-"]').click(function () {
            const dropdownId = $(this).attr('id').replace('view-submission-', 'dropdown-');
            toggleDropdown(dropdownId);
        });
    // ----------------------------- Attach toggleDropdown to view submission buttons -----------------------------

    // ----------------------------- Handle view explanation button click -----------------------------
        $('.view-explanation-btn').click(function () {
            const explanation = $(this).data('explanation');
            const answerExplanation = $('#answer_explanation');

            $('#explanationModal').removeClass('hidden');

            if (!explanation) {
                answerExplanation.text("No explanation provided.");
            } else {
                answerExplanation.html(makeLinksClickable(explanation));
            }
        });
    // ----------------------------- Handle view explanation button click -----------------------------

    // ----------------------------- Handle cancel explanation button click -----------------------------
        $('#cancelExplanation').click(function () {
            $('#explanationModal').addClass('hidden');
        });
    // ----------------------------- Handle cancel explanation button click -----------------------------

    // ----------------------------- Handle close explanation button -----------------------------
        // $('#explanationModalClose').click(function () {
        //     $('#explanationModal').classList.add('hidden');
        // });

        document.getElementById('explanationModalClose').addEventListener('click', function() {
            // closePdfModal();
            const explanationModal = document.getElementById('explanationModal');
            explanationModal.classList.add('hidden');
            console.log("explanationModalClose clicked");
        });
    // ----------------------------- Handle submit for review confirmation modal close -----------------------------

    // ----------------------------- Helper function to make links clickable in explanations -----------------------------
        function makeLinksClickable(text) {
            const urlRegex = /(https?:\/\/[^\s]+)/g;
            
            // Replace links first
            let formattedText = text.replace(urlRegex, function (url) {
                return `<a href="${url}" target="_blank" class="text-blue-500 underline">${url}</a>`;
            });

            // Replace newlines with `<br>` to preserve formatting
            formattedText = formattedText.replace(/\n/g, '<br>');

            return formattedText;
        }
    // ----------------------------- Helper function to make links clickable in explanations -----------------------------

    // ----------------------------- Handle view pdf button click -----------------------------
        document.querySelectorAll('.view-pdf-btn').forEach(button => {
            button.addEventListener('click', function() {
                

                const pdfFile = this.getAttribute('data-pdf');
                const pdfViewer = document.getElementById('pdfViewer');
                const pdfModal = document.getElementById('pdfModal');

                // Set the iframe source to the PDF file
                pdfViewer.src = `./view-pdf?file=${pdfFile}`;

                console.log(pdfViewer.src)

                // Show the modal
                pdfModal.classList.remove('hidden');
            });
        });
    // ----------------------------- Handle view pdf button click -----------------------------

    

    // ----------------------------- Handle close pdf modal button click -----------------------------
        document.getElementById('closePdfModal').addEventListener('click', function() {
            closePdfModal();
        });

        function closePdfModal() {
            const pdfModal = document.getElementById('pdfModal');
            const pdfViewer = document.getElementById('pdfViewer');

            // Hide the modal and reset the iframe source
            pdfModal.classList.add('hidden');
            pdfViewer.src = '';
        }
    // ----------------------------- Handle close pdf modal button click -----------------------------

    
    // --------------------------------------------------------------------------------------------------------



    // --------------------------------------------------------------------------------------------------------
    // HANDLE DRAFT
    // ----------------------------- Handle draft button click -----------------------------
        $('.draftBtn').click(function () {
            console.log("Hellooooooooo")
            const currentQuestionId = $(this).data('question-id');
            const currentQuestionnaireId = $(this).data('questionnaire-id');
            const draftBtn = $(this); // Store reference to the button

            // Check if the user has selected an answer
            const answer = $(`input[name="answer_${currentQuestionId}"]:checked`).val();
            const explanation = $(`textarea[name="answer_explanation_${currentQuestionId}"]`).val();
            
            console.log("explanation: ", explanation);

            if (!answer) {
                showToast('Please select an answer (yes or no)', 'error');
                return;
            }

            // There should be an explanation about your answer or uploaded file/s
            if (!explanation || explanation.trim() === '') {
                showToast('Please enter an explanation or context about your answer and/or uploaded file/s.', 'error');
                return;
            }

            // Check if explanation is all numbers
            if (explanation && /^\d+$/.test(explanation.trim())) {
                showToast('Explanation cannot consist only of numbers.', 'error');
                return;
            }

            // Create form data for submission
            const formSelector = $(`#form-${currentQuestionId}`).length ? `#form-${currentQuestionId}` : `#form-reject-${currentQuestionId}`;
            const form = $(formSelector);
            const formData = new FormData(form[0]);
            formData.append('action', 'draft');

            $.ajax({
                // url: '/questionnaire/answer/save-draft',
                url: '/questionnaire/answer-action',
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                success: function (response) {
                    try {
                        console.log("Response from draft submission:", response);
                        
                        if (response.success) {
                            // Show success toast
                            showToast('Draft saved successfully!', 'success');
                            
                            // After successful submission, reload the page after a short delay
                            setTimeout(function() {
                                location.reload();
                            }, 2000); // 1.2 seconds delay so user sees the toast

                        } else if (response.errors) {
                            // Re-enable the draft button on error
                            draftBtn.prop('disabled', false).removeClass('opacity-50 cursor-not-allowed');
                            // Show error toast
                            showToast('Error: '+ (response.errors || 'Unknown error occurred'), 'error');
                        }
                    } catch (e) {
                        // Re-enable the draft button on error
                        draftBtn.prop('disabled', false).removeClass('opacity-50 cursor-not-allowed');
                        // Show error toast
                        showToast('Error: '+ e.message, 'error');
                    }
                },
                error: function (xhr, status, error) {
                    // Re-enable the draft button on error
                    draftBtn.prop('disabled', false).removeClass('opacity-50 cursor-not-allowed');
                    // Show error toast
                    showToast('Error saving draft. Try again later.', 'error');
                }
            });
        });
    // ----------------------------- Handle draft button click -----------------------------



    // --------------------------------------------------------------------------------------------------------
    // HANDLE SUBMIT FOR REVIEW
    // ----------------------------- Handle submit for review button click -----------------------------
        $('.submitForReviewBtn, .submitForReviewBtnReject').click(function () {
            currentQuestionId = $(this).data('question-id');
            currentQuestionnaireId = $(this).data('questionnaire-id');

            // Check if the user has selected an answer and entered an explanation
            const answer = $(`input[name="answer_${currentQuestionId}"]:checked`).val();
            const explanation = $(`textarea[name="answer_explanation_${currentQuestionId}"]`).val();
            const files = $(`input[name="files_${currentQuestionId}[]"]`)[0].files;

            if (!answer) {
                // alert('Please select an answer');
                showToast('Please select an answer (yes or no)', 'error');
                return;
            }

            // There should be an explanation about your answer or uploaded file/s
            if (!explanation || explanation.trim() === '') {
                showToast('Please enter an explanation or context about your answer and/or uploaded file/s.', 'error');
                return;
            }

            // Check if explanation is all numbers
            if (explanation && /^\d+$/.test(explanation.trim())) {
                showToast('Explanation cannot consist only of numbers.', 'error');
                return;
            }

            // Show confirmation modal
            // $('#submitForReviewConfirmationModal').show();
            $('#submitForReviewConfirmationModal').removeClass('hidden');
        });
    // ----------------------------- Handle submit for review button click -----------------------------
    
    // ----------------------------- Handle submit for review confirmation modal buttons (No) -----------------------------
        $('#submitForReviewConfirmNo').click(function () {
            $('#submitForReviewConfirmationModal').hide();
        });
    // ----------------------------- Handle submit for review confirmation modal buttons (No) -----------------------------

    // ----------------------------- Handle submit for review confirmation modal close -----------------------------
        $('#submitForReviewClose').click(function () {
            $('#submitForReviewConfirmationModal').hide();
        });
    // ----------------------------- Handle submit for review confirmation modal close -----------------------------

    // ----------------------------- Handle for review submit confirmation modal buttons (Yes) -----------------------------
        $('#submitForReviewConfirmYes').click(function () {

            // Disable all buttons to prevent duplicate submissions
            $('#submitForReviewConfirmYes').prop('disabled', true).addClass('opacity-50 cursor-not-allowed');
            $('#submitForReviewConfirmationModal').prop('disabled', true).addClass('opacity-50 cursor-not-allowed');
            $('#closeConfirmModal').prop('disabled', true).addClass('opacity-50 cursor-not-allowed');
            
            // Show loading indicator
            $('#submitForReviewLoadingIndicator').removeClass('hidden');

            const formSelector = $(`#form-${currentQuestionId}`).length ? `#form-${currentQuestionId}` : `#form-reject-${currentQuestionId}`;
            const form = $(formSelector);
            const formData = new FormData(form[0]);
            formData.append('action', 'for-review');

            const answer = $(`input[name="answer_${currentQuestionId}"]:checked`).val();
            const explanation = $(`textarea[name="answer_explanation_${currentQuestionId}"]`).val();

            $.ajax({
                url: '/questionnaire/answer-action',
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                success: function (response) {

                    // Enable all buttons
                    $('#submitForReviewConfirmYes').prop('disabled', false).removeClass('opacity-50 cursor-not-allowed');
                    $('#submitForReviewConfirmNo').prop('disabled', false).removeClass('opacity-50 cursor-not-allowed');
                    // $('#submitForReviewClose').prop('disabled', false).removeClass('opacity-50 cursor-not-allowed');
                    $('#closeConfirmModal').prop('disabled', false).removeClass('opacity-50 cursor-not-allowed');

                    // Hide loading indicator
                    // $('#submitForReviewLoadingIndicator').addClass('hidden');
                    $('#loadingIndicator').addClass('hidden');

                    try {
                        console.log("Response from answer submission:", response);
                        
                        if (response.success) {
                            //  hide the modal
                            $('#submitForReviewConfirmationModal').hide();

                            // Show success toast
                            showToast('Answer submitted successfully!', 'success');

                            // After successful submission, reload the page after a short delay
                            setTimeout(function() {
                                location.reload();
                            }, 2000); // 1.2 seconds delay so user sees the toast
                        } else if (response.errors) {
                            // hide the modal
                            $('#submitForReviewConfirmationModal').hide();

                            // Show error toast
                            showToast('Error: '+ (response.errors || 'Unknown error occurred'), 'error');
                            
                            setTimeout(function() {
                                location.reload();
                            }, 2000);
                        }
                    } catch (e) {
                        //  hide the modal
                        $('#submitConfirmationModal').hide();

                        // Show error toast
                        showToast('Error: '+ e.message, 'error');
                        // $('#submitConfirmationModal').hide();
                    }
                },
                error: function (xhr, status, error) {
                    //  hide the modal
                    $('#submitConfirmationModal').hide();

                    // Show error toast
                    showToast('Error submitting answer. Try again later.', 'error');
                }
            });
        });
    // ----------------------------- Handle for review submit confirmation modal buttons (Yes) -----------------------------
    // --------------------------------------------------------------------------------------------------------



    // --------------------------------------------------------------------------------------------------------
    // HANDLE SUBMIT
    // ----------------------------- Handle submit button click -----------------------------
        $('.submitBtn, .submitBtnReject').click(function () {
            currentQuestionId = $(this).data('question-id');
            currentQuestionnaireId = $(this).data('questionnaire-id');

            // Check if the user has selected an answer and entered an explanation
            const answer = $(`input[name="answer_${currentQuestionId}"]:checked`).val();
            const explanation = $(`textarea[name="answer_explanation_${currentQuestionId}"]`).val();
            const files = $(`input[name="files_${currentQuestionId}[]"]`)[0].files;

            if (!answer) {
                // alert('Please select an answer');
                showToast('Please select an answer (yes or no)', 'error');
                return;
            }
            
            // There should be an explanation about your answer or uploaded file/s
            if (!explanation || explanation.trim() === '') {
                alert('Please enter an explanation or context about your answer and/or uploaded file/s.', 'error');
                return;
            }

            // Check if explanation is all numbers
            if (explanation && /^\d+$/.test(explanation.trim())) {
                alert('Explanation cannot consist only of numbers.', 'error');
                return;
            }

            // Show confirmation modal
            $('#submitConfirmationModal').show();
        });
    // ----------------------------- Handle submit button click -----------------------------

    // ----------------------------- Handle submit confirmation modal buttons (No) -----------------------------
        $('#confirmNo').click(function () {
            $('#submitConfirmationModal').hide();
        });
    // ----------------------------- Handle submit confirmation modal buttons (No) -----------------------------

    // ----------------------------- Handle submit confirmation modal buttons (Yes) -----------------------------
        $('#confirmYes').click(function () {

            // Disable all buttons to prevent duplicate submissions
            $('#confirmYes').prop('disabled', true).addClass('opacity-50 cursor-not-allowed');
            $('#confirmNo').prop('disabled', true).addClass('opacity-50 cursor-not-allowed');
            $('#closeConfirmModal').prop('disabled', true).addClass('opacity-50 cursor-not-allowed');
            
            // Show loading indicator
            $('#loadingIndicator').removeClass('hidden');

            const formSelector = $(`#form-${currentQuestionId}`).length ? `#form-${currentQuestionId}` : `#form-reject-${currentQuestionId}`;
            const form = $(formSelector);
            const formData = new FormData(form[0]);
            formData.append('action', 'submit');

            const answer = $(`input[name="answer_${currentQuestionId}"]:checked`).val();
            const explanation = $(`textarea[name="answer_explanation_${currentQuestionId}"]`).val();

            $.ajax({
                url: '/questionnaire/answer-action',
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                success: function (response) {

                    // Enable all buttons
                    $('#confirmYes').prop('disabled', false).removeClass('opacity-50 cursor-not-allowed');
                    $('#confirmNo').prop('disabled', false).removeClass('opacity-50 cursor-not-allowed');
                    $('#closeConfirmModal').prop('disabled', false).removeClass('opacity-50 cursor-not-allowed');

                    // Hide loading indicator
                    $('#loadingIndicator').addClass('hidden');

                    try {
                        console.log("Response from answer submission:", response);
                        
                        if (response.success) {
                            //  hide the modal
                            $('#submitConfirmationModal').hide();

                            // Show success toast
                            showToast('Answer submitted successfully!', 'success');

                            // After successful submission, reload the page after a short delay
                            setTimeout(function() {
                                location.reload();
                            }, 2000); // 1.2 seconds delay so user sees the toast
                        } else if (response.errors) {
                            // hide the modal
                            $('#submitConfirmationModal').hide();

                            // Show error toast
                            showToast('Error: '+ (response.errors || 'Unknown error occurred'), 'error');
                        }
                    } catch (e) {
                        //  hide the modal
                        $('#submitConfirmationModal').hide();

                        // Show error toast
                        showToast('Error: '+ e.message, 'error');
                        // $('#submitConfirmationModal').hide();
                    }
                },
                error: function (xhr, status, error) {
                    //  hide the modal
                    $('#submitConfirmationModal').hide();

                    // Show error toast
                    showToast('Error submitting answer. Try again later.', 'error');
                }
            });
        });
    // ----------------------------- Handle submit confirmation modal buttons (Yes) -----------------------------
    // --------------------------------------------------------------------------------------------------------


    // ----------------------------- 
        // Get all modals and the sticky thead
        const modals = document.querySelectorAll('#pdfModal, #uploadDhVerificationModal, #uploadNotAllowedModal, #noForReviewModal, #submitConfirmationModal, #submitForReviewConfirmationModal, #explanationModal');
        const stickyThead = document.querySelector('thead.sticky');
    
        // Function to check if any modal is visible
        function isAnyModalVisible() {
            for (const modal of modals) {

                // print the modal name
                console.log(modal.id + " is the name of the modal, and this is the classList.contains('hidden')" + modal.classList)
                // modal.classList.contains('hidden')

                // Check both classList.contains('hidden') and jQuery's :visible selector
                if (!modal.classList.contains('hidden') && $(modal).is(':visible')) {
                    return true;
                }
            }
            return false;
        }
        
        // Function to toggle thead visibility based on modal state
        function toggleTheadVisibility() {
            if (stickyThead) {
                // console.log("sticky thead")
                // console.log(stickyThead)
                if (isAnyModalVisible()) {
                    stickyThead.style.display = 'none';
                    // console.log("isAnyModalVisible is true");
                    console.log("Hide thead")
                } else {
                    stickyThead.style.display = '';
                    // console.log("isAnyModalVisible is false");
                    console.log("Show thead")
                }
            }
        }
        
        // Add event listeners to all modal open/close buttons
        document.querySelectorAll('[id$="ModalBtn"], [id^="submit"], [id^="open"], [id^="view"], .close-modal, [id^="close"]').forEach(button => {
            button.addEventListener('click', function() {
                // Use setTimeout to allow the modal's display state to update first
                setTimeout(toggleTheadVisibility, 0);
                console.log("Modal button clicked");
            });
        });
        
    
        // Also attach to jQuery show/hide events
        // $(document).on('show hide', '.modal', function() {
        //     setTimeout(toggleTheadVisibility, 0);
        // });


        // Initial check
        // toggleTheadVisibility();
    // ----------------------------- 
});
