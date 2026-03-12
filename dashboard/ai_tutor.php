<?php
require_once '../config/database.php';

if (!isLoggedIn()) {
    redirect('../login.php');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Tutor - Skillify</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
            background: #f5f7fb;
        }
        
        .navbar {
            background: white;
            padding: 20px 40px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .logo {
            font-size: 24px;
            font-weight: bold;
            color: #667eea;
        }
        
        .nav-links a {
            text-decoration: none;
            color: #333;
            font-weight: 500;
            margin-left: 30px;
        }
        
        .container {
            max-width: 1000px;
            margin: 40px auto;
            padding: 0 20px;
        }
        
        .ai-header {
            text-align: center;
            margin-bottom: 40px;
        }
        
        .ai-header h1 {
            font-size: 36px;
            color: #333;
            margin-bottom: 10px;
        }
        
        .ai-header p {
            color: #666;
            font-size: 18px;
        }
        
        .chat-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            overflow: hidden;
            height: 600px;
            display: flex;
            flex-direction: column;
        }
        
        .chat-messages {
            flex: 1;
            padding: 30px;
            overflow-y: auto;
        }
        
        .message {
            display: flex;
            margin-bottom: 20px;
            animation: fadeIn 0.3s;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .message-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #667eea;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            font-weight: bold;
        }
        
        .message.user .message-avatar {
            background: #48bb78;
        }
        
        .message-content {
            flex: 1;
            background: #f5f7fb;
            padding: 15px 20px;
            border-radius: 20px;
            border-top-left-radius: 0;
            line-height: 1.6;
            color: #333;
        }
        
        .message.user .message-content {
            background: #667eea;
            color: white;
            border-top-left-radius: 20px;
            border-top-right-radius: 0;
        }
        
        .chat-input {
            padding: 20px;
            border-top: 2px solid #f0f0f0;
            display: flex;
            gap: 15px;
        }
        
        .chat-input input {
            flex: 1;
            padding: 15px 20px;
            border: 2px solid #e1e1e1;
            border-radius: 10px;
            font-size: 16px;
            transition: border-color 0.3s;
        }
        
        .chat-input input:focus {
            outline: none;
            border-color: #667eea;
        }
        
        .chat-input button {
            padding: 15px 30px;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s;
        }
        
        .chat-input button:hover {
            background: #5a67d8;
        }
        
        .chat-input button:disabled {
            background: #ccc;
            cursor: not-allowed;
        }
        
        .typing-indicator {
            display: flex;
            gap: 5px;
            padding: 15px 20px;
            background: #f5f7fb;
            border-radius: 20px;
            width: fit-content;
        }
        
        .typing-indicator span {
            width: 8px;
            height: 8px;
            background: #999;
            border-radius: 50%;
            animation: typing 1s infinite ease-in-out;
        }
        
        .typing-indicator span:nth-child(2) {
            animation-delay: 0.2s;
        }
        
        .typing-indicator span:nth-child(3) {
            animation-delay: 0.4s;
        }
        
        @keyframes typing {
            0%, 60%, 100% { transform: translateY(0); }
            30% { transform: translateY(-10px); }
        }
        
        .suggestions {
            display: flex;
            gap: 10px;
            margin-top: 20px;
            flex-wrap: wrap;
        }
        
        .suggestion-btn {
            padding: 10px 20px;
            background: white;
            border: 2px solid #e1e1e1;
            border-radius: 25px;
            cursor: pointer;
            font-size: 14px;
            transition: all 0.3s;
            color: #666;
        }
        
        .suggestion-btn:hover {
            border-color: #667eea;
            color: #667eea;
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="logo">Skillify</div>
        <div class="nav-links">
            <a href="user_dashboard.php">Dashboard</a>
            <a href="courses.php">Courses</a>
            <a href="ai_tutor.php" style="color: #667eea;">AI Tutor</a>
            <a href="../logout.php" style="color: #666;">Logout</a>
        </div>
    </nav>
    
    <div class="container">
        <div class="ai-header">
            <h1>🤖 AI Learning Assistant</h1>
            <p>Ask me anything about digital skills, AI, or your courses!</p>
        </div>
        
        <div class="chat-container">
            <div class="chat-messages" id="chatMessages">
                <div class="message">
                    <div class="message-avatar">AI</div>
                    <div class="message-content">
                        Hi! I'm your AI learning assistant. I can help you with:
                        <ul style="margin-top: 10px; margin-left: 20px;">
                            <li>Understanding course concepts</li>
                            <li>Explaining technical terms</li>
                            <li>Providing learning resources</li>
                            <li>Answering questions about AI and digital skills</li>
                        </ul>
                        What would you like to learn about today?
                    </div>
                </div>
            </div>
            
            <div class="suggestions" id="suggestions">
                <button class="suggestion-btn" onclick="addSuggestion('What is machine learning?')">What is machine learning?</button>
                <button class="suggestion-btn" onclick="addSuggestion('How do I stay safe online?')">How do I stay safe online?</button>
                <button class="suggestion-btn" onclick="addSuggestion('Explain neural networks simply')">Explain neural networks simply</button>
                <button class="suggestion-btn" onclick="addSuggestion('What are the best AI tools?')">What are the best AI tools?</button>
            </div>
            
            <div class="chat-input">
                <input type="text" id="userInput" placeholder="Type your question here..." onkeypress="handleKeyPress(event)">
                <button onclick="sendMessage()" id="sendButton">Send</button>
            </div>
        </div>
    </div>
    
    <script>
        const chatMessages = document.getElementById('chatMessages');
        const userInput = document.getElementById('userInput');
        const sendButton = document.getElementById('sendButton');
        
        function addSuggestion(text) {
            userInput.value = text;
            sendMessage();
        }
        
        function handleKeyPress(event) {
            if (event.key === 'Enter') {
                sendMessage();
            }
        }
        
        async function sendMessage() {
            const message = userInput.value.trim();
            if (!message) return;
            
            // Add user message
            addMessage(message, 'user');
            userInput.value = '';
            
            // Show typing indicator
            showTypingIndicator();
            
            try {
                // For hackathon demo, we'll simulate AI responses
                // You can replace this with actual API call to OpenAI/Gemini
                const response = await getAIResponse(message);
                
                // Remove typing indicator
                removeTypingIndicator();
                
                // Add AI response
                addMessage(response, 'ai');
            } catch (error) {
                removeTypingIndicator();
                addMessage('Sorry, I encountered an error. Please try again.', 'ai');
            }
        }
        
        function addMessage(text, sender) {
            const messageDiv = document.createElement('div');
            messageDiv.className = `message ${sender}`;
            
            const avatar = document.createElement('div');
            avatar.className = 'message-avatar';
            avatar.textContent = sender === 'user' ? 'You' : 'AI';
            
            const content = document.createElement('div');
            content.className = 'message-content';
            content.textContent = text;
            
            messageDiv.appendChild(avatar);
            messageDiv.appendChild(content);
            chatMessages.appendChild(messageDiv);
            
            // Scroll to bottom
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }
        
        function showTypingIndicator() {
            const indicator = document.createElement('div');
            indicator.className = 'message';
            indicator.id = 'typingIndicator';
            
            const avatar = document.createElement('div');
            avatar.className = 'message-avatar';
            avatar.textContent = 'AI';
            
            const typingDiv = document.createElement('div');
            typingDiv.className = 'typing-indicator';
            typingDiv.innerHTML = '<span></span><span></span><span></span>';
            
            indicator.appendChild(avatar);
            indicator.appendChild(typingDiv);
            chatMessages.appendChild(indicator);
            
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }
        
        function removeTypingIndicator() {
            const indicator = document.getElementById('typingIndicator');
            if (indicator) {
                indicator.remove();
            }
        }
        
        // Simulated AI responses (replace with actual API call)
        async function getAIResponse(message) {
            // Simple keyword-based responses for demo
            const lowerMessage = message.toLowerCase();
            
            if (lowerMessage.includes('machine learning')) {
                return "Machine learning is a subset of AI where computers learn from data without being explicitly programmed. Think of it like teaching a child to recognize animals - you show them many examples until they can identify them on their own!";
            }
            else if (lowerMessage.includes('online safety') || lowerMessage.includes('safe online')) {
                return "Great question! Here are key online safety tips:\n\n1. Use strong, unique passwords\n2. Enable two-factor authentication\n3. Be careful what you share on social media\n4. Don't click suspicious links\n5. Keep your software updated\n\nWould you like me to elaborate on any of these?";
            }
            else if (lowerMessage.includes('neural network')) {
                return "Neural networks are computing systems inspired by the human brain. They consist of layers of interconnected nodes (neurons) that process information. Each connection has a weight that adjusts as the network learns, allowing it to recognize patterns and make decisions!";
            }
            else if (lowerMessage.includes('ai tools')) {
                return "Popular AI tools include:\n\n• ChatGPT - For conversation and content\n• Google Gemini - Google's AI assistant\n• GitHub Copilot - For coding help\n• DALL-E - For image generation\n• Grammarly - For writing assistance\n\nEach tool has unique strengths. What would you like to know more about?";
            }
            else if (lowerMessage.includes('hello') || lowerMessage.includes('hi')) {
                return "Hello! 👋 I'm here to help you learn about digital skills and AI. What topic would you like to explore today?";
            }
            else {
                return "That's an interesting question! To give you the best answer, could you please provide more details about what you'd like to learn? For example, are you interested in AI basics, digital literacy, or something specific about the courses?";
            }
        }
    </script>
</body>
</html>