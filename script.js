// --- Core Send Message Function ---
async function sendMessage() {
    const chatInput = document.getElementById('chatInput');
    const query = chatInput.value.trim();
    if (!query) return;

    // 1. Display user message and clear input
    appendMessage('user', query);
    chatInput.value = '';

    // 2. Display loading indicator
    appendLoadingMessage();

    try {
        // 3. Send query to Flask backend
        const response = await fetch('/chat_assistant', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ query: query })
        });

        const data = await response.json();

        // 4. Remove loading indicator
        removeLoadingMessage();

        // 5. Display AI response
        if (response.ok) {
            appendMessage('ai', data.response, data.sources);
        } else {
            // Handle API errors (e.g., GEMINI_API_KEY missing)
            appendMessage('ai', data.response || 'Failed to connect to AI Assistant. Check server logs.', []);
        }
    } catch (error) {
        // 6. Handle network errors
        removeLoadingMessage();
        appendMessage('ai', 'Network Error: Could not reach the server.', []);
        console.error('Chat error:', error);
    }
}

// --- Helper Functions ---

function appendMessage(sender, text, sources = []) {
    const chatWindow = document.getElementById('chatWindow');
    const messageDiv = document.createElement('div');
    messageDiv.className = `chat-message ${sender}-message`;

    const textElement = document.createElement('p');
    // Basic markdown replacement for visual clarity
    textElement.innerHTML = text.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>').replace(/\*(.*?)\*/g, '<em>$1</em>');
    messageDiv.appendChild(textElement);

    if (sender === 'ai' && sources.length > 0) {
        const sourcesDiv = document.createElement('div');
        sourcesDiv.className = 'sources';
        sourcesDiv.innerHTML = '<strong>Sources:</strong>';
        sources.forEach((source, index) => {
            const link = document.createElement('a');
            link.href = source.uri;
            link.target = '_blank';
            link.title = source.title || source.uri;
            link.textContent = `[${index + 1}] ${source.title || source.uri}`;
            sourcesDiv.appendChild(link);
        });
        messageDiv.appendChild(sourcesDiv);
    }

    chatWindow.appendChild(messageDiv);
    chatWindow.scrollTop = chatWindow.scrollHeight;
}

function appendLoadingMessage() {
    const chatWindow = document.getElementById('chatWindow');
    const loadingDiv = document.createElement('div');
    loadingDiv.className = 'chat-message loading-message';
    loadingDiv.id = 'loadingMessage';
    loadingDiv.textContent = 'Assistant is typing...';
    chatWindow.appendChild(loadingDiv);
    chatWindow.scrollTop = chatWindow.scrollHeight;
}

function removeLoadingMessage() {
    const loadingDiv = document.getElementById('loadingMessage');
    if (loadingDiv) {
        loadingDiv.remove();
    }
}

// --- Event Listeners: Attach events safely after the DOM is ready ---
document.addEventListener('DOMContentLoaded', () => {
    const sendButton = document.getElementById('sendButton');
    const chatInput = document.getElementById('chatInput');

    // 1. Attach listener to the Send button
    if (sendButton) {
        sendButton.addEventListener('click', sendMessage);
    }

    // 2. Attach listener to the Enter key in the text field
    if (chatInput) {
        chatInput.addEventListener('keypress', (event) => {
            if (event.key === 'Enter') {
                event.preventDefault(); // Prevent accidental form submission
                sendMessage();
            }
        });
    }
});

