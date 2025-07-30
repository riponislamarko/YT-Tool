document.addEventListener('DOMContentLoaded', () => {
    const checkBtn = document.getElementById('check-btn');
    const channelInput = document.getElementById('channel-input');
    const resultContainer = document.getElementById('result-container');

    checkBtn.addEventListener('click', async () => {
        const channelUrl = channelInput.value.trim();
        if (!channelUrl) {
            resultContainer.innerHTML = '<p style="color: red;">Please enter a channel URL or handle.</p>';
            resultContainer.classList.add('visible');
            return;
        }

        // Show loader and disable button
        resultContainer.innerHTML = '<div class="loader"></div>';
        resultContainer.classList.add('visible');
        checkBtn.disabled = true;

        try {
            const response = await fetch('/check', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ channelUrl }),
            });

            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.error || 'An unknown error occurred.');
            }

            renderResults(data);

        } catch (error) {
            resultContainer.innerHTML = `<p style="color: red;">Error: ${error.message}</p>`;
        } finally {
            checkBtn.disabled = false;
        }
    });

    function renderResults(data) {
        let detailsHtml = '<ul>';
        for (const [key, value] of Object.entries(data.details)) {
            detailsHtml += `<li>
                <span>${formatDetailKey(key)}</span>
                <strong>${value.found ? '✅ Detected' : '❌ Not Found'} (+${value.points} pts)</strong>
            </li>`;
        }
        detailsHtml += '</ul>';

        const resultHtml = `
            <div class="result-status">${data.status} (Score: ${data.score})</div>
            <div class="result-details">
                <h4>Detection Details:</h4>
                ${detailsHtml}
            </div>
        `;
        resultContainer.innerHTML = resultHtml;
    }

    function formatDetailKey(key) {
        return key.replace(/([A-Z])/g, ' $1').replace(/^./, str => str.toUpperCase());
    }
});
