<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🏨 AI Hotel Reservation Assistant</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary: #3498db;
            --secondary: #2c3e50;
            --accent: #e74c3c;
            --success: #2ecc71;
            --warning: #f39c12;
            --light: #ecf0f1;
            --dark: #34495e;
            --text: #2c3e50;
            --text-light: #7f8c8d;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
            color: var(--text);
        }

        .container {
            display: flex;
            width: 100%;
            max-width: 1400px;
            height: 90vh;
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        .sidebar {
            width: 300px;
            background: var(--secondary);
            color: white;
            padding: 25px;
            display: flex;
            flex-direction: column;
            overflow-y: auto;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 30px;
        }

        .logo i {
            font-size: 2em;
            color: var(--primary);
        }

        .logo h1 {
            font-size: 1.4em;
            font-weight: 600;
        }

        .nav-section {
            margin-bottom: 30px;
        }

        .nav-section h3 {
            font-size: 1em;
            margin-bottom: 15px;
            color: var(--light);
            opacity: 0.8;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .nav-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 15px;
            border-radius: 10px;
            margin-bottom: 8px;
            cursor: pointer;
            transition: all 0.3s;
        }

        .nav-item:hover, .nav-item.active {
            background: rgba(255,255,255,0.1);
        }

        .nav-item i {
            width: 20px;
            text-align: center;
        }

        .stats {
            background: rgba(255,255,255,0.1);
            border-radius: 15px;
            padding: 20px;
            margin-top: auto;
        }

        .stat-item {
            display: flex;
            justify-content: between;
            margin-bottom: 12px;
        }

        .stat-label {
            flex: 1;
            font-size: 0.9em;
            opacity: 0.8;
        }

        .stat-value {
            font-weight: 600;
        }

        .main-content {
            flex: 1;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .header {
            padding: 20px 30px;
            border-bottom: 1px solid #e1e5e9;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header h2 {
            font-size: 1.5em;
            font-weight: 600;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
        }

        .chat-area {
            flex: 1;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .chat-tabs {
            display: flex;
            border-bottom: 1px solid #e1e5e9;
            background: #f8f9fa;
        }

        .chat-tab {
            padding: 15px 25px;
            cursor: pointer;
            border-bottom: 3px solid transparent;
            transition: all 0.3s;
        }

        .chat-tab.active {
            border-bottom-color: var(--primary);
            color: var(--primary);
            font-weight: 600;
        }

        .chat-messages {
            flex: 1;
            padding: 20px;
            overflow-y: auto;
            background: #f8f9fa;
        }

        .message {
            margin-bottom: 20px;
            display: flex;
            align-items: flex-start;
            gap: 12px;
            animation: fadeIn 0.3s ease-in;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .message.user {
            flex-direction: row-reverse;
        }

        .avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            flex-shrink: 0;
        }

        .user .avatar {
            background: var(--primary);
            color: white;
        }

        .bot .avatar {
            background: var(--accent);
            color: white;
        }

        .message-content {
            max-width: 70%;
            padding: 15px 20px;
            border-radius: 18px;
            line-height: 1.5;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }

        .user .message-content {
            background: var(--primary);
            color: white;
            border-bottom-right-radius: 4px;
        }

        .bot .message-content {
            background: white;
            color: var(--text);
            border: 1px solid #e1e5e9;
            border-bottom-left-radius: 4px;
        }

        .message-time {
            font-size: 0.75em;
            opacity: 0.7;
            margin-top: 5px;
        }

        .typing-indicator {
            display: none;
            padding: 15px 20px;
            background: white;
            border-radius: 18px;
            border-bottom-left-radius: 4px;
            margin-bottom: 20px;
            align-items: center;
            gap: 10px;
            max-width: 70%;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }

        .typing-dots {
            display: flex;
            gap: 4px;
        }

        .typing-dot {
            width: 8px;
            height: 8px;
            background: var(--text-light);
            border-radius: 50%;
            animation: typing 1.4s infinite;
        }

        .typing-dot:nth-child(2) { animation-delay: 0.2s; }
        .typing-dot:nth-child(3) { animation-delay: 0.4s; }

        @keyframes typing {
            0%, 60%, 100% { transform: translateY(0); }
            30% { transform: translateY(-5px); }
        }

        .chat-input-container {
            padding: 20px;
            background: white;
            border-top: 1px solid #e1e5e9;
            display: flex;
            gap: 12px;
        }

        .chat-input {
            flex: 1;
            padding: 15px 20px;
            border: 2px solid #e1e5e9;
            border-radius: 25px;
            outline: none;
            font-size: 1em;
            transition: border-color 0.3s;
        }

        .chat-input:focus {
            border-color: var(--primary);
        }

        .action-button {
            padding: 15px 20px;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 25px;
            cursor: pointer;
            font-size: 1em;
            transition: background 0.3s;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .action-button:hover {
            background: #2980b9;
        }

        .action-button:disabled {
            background: #bdc3c7;
            cursor: not-allowed;
        }

        .quick-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            padding: 0 20px 15px;
            background: white;
        }

        .quick-action {
            padding: 10px 18px;
            background: var(--light);
            border: 1px solid #bdc3c7;
            border-radius: 20px;
            font-size: 0.9em;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .quick-action:hover {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
        }

        .feature-panel {
            width: 350px;
            background: white;
            border-left: 1px solid #e1e5e9;
            padding: 25px;
            overflow-y: auto;
        }

        .feature-header {
            margin-bottom: 25px;
            text-align: center;
        }

        .feature-header h3 {
            font-size: 1.3em;
            margin-bottom: 8px;
        }

        .feature-header p {
            color: var(--text-light);
            font-size: 0.9em;
        }

        .feature-content {
            margin-bottom: 30px;
        }

        .feature-section {
            margin-bottom: 25px;
        }

        .feature-section h4 {
            font-size: 1.1em;
            margin-bottom: 15px;
            padding-bottom: 8px;
            border-bottom: 1px solid #e1e5e9;
        }

        .upload-area {
            border: 2px dashed #bdc3c7;
            border-radius: 15px;
            padding: 30px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
            margin-bottom: 20px;
        }

        .upload-area:hover {
            border-color: var(--primary);
            background: #f8f9fa;
        }

        .upload-area i {
            font-size: 2.5em;
            color: var(--text-light);
            margin-bottom: 15px;
        }

        .upload-text {
            font-size: 1em;
            margin-bottom: 10px;
        }

        .upload-subtext {
            font-size: 0.85em;
            color: var(--text-light);
        }

        .inspection-results {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 20px;
            margin-top: 20px;
        }

        .inspection-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 12px;
            padding-bottom: 12px;
            border-bottom: 1px solid #e1e5e9;
        }

        .inspection-item:last-child {
            margin-bottom: 0;
            padding-bottom: 0;
            border-bottom: none;
        }

        .inspection-label {
            font-weight: 500;
        }

        .inspection-score {
            font-weight: 600;
        }

        .score-excellent { color: var(--success); }
        .score-good { color: var(--primary); }
        .score-fair { color: var(--warning); }
        .score-poor { color: var(--accent); }

        .sentiment-display {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-top: 15px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 15px;
        }

        .sentiment-icon {
            font-size: 2em;
        }

        .sentiment-positive { color: var(--success); }
        .sentiment-neutral { color: var(--warning); }
        .sentiment-negative { color: var(--accent); }

        .sentiment-details {
            flex: 1;
        }

        .sentiment-label {
            font-weight: 600;
            margin-bottom: 5px;
        }

        .sentiment-value {
            font-size: 0.9em;
            color: var(--text-light);
        }

        /* Responsive Design */
        @media (max-width: 1200px) {
            .container {
                flex-direction: column;
                height: auto;
            }
            
            .sidebar {
                width: 100%;
                flex-direction: row;
                flex-wrap: wrap;
                padding: 15px;
            }
            
            .logo {
                margin-bottom: 0;
                margin-right: 30px;
            }
            
            .nav-section {
                margin-bottom: 0;
                margin-right: 30px;
            }
            
            .stats {
                margin-top: 0;
                margin-left: auto;
            }
            
            .feature-panel {
                width: 100%;
                border-left: none;
                border-top: 1px solid #e1e5e9;
            }
        }

        @media (max-width: 768px) {
            .sidebar {
                flex-direction: column;
            }
            
            .stats {
                margin-left: 0;
                margin-top: 20px;
            }
            
            .message-content {
                max-width: 85%;
            }
        }

        @media (max-width: 480px) {
            .container {
                border-radius: 0;
                height: 100vh;
            }
            
            .header {
                padding: 15px;
            }
            
            .chat-tab {
                padding: 12px 15px;
                font-size: 0.9em;
            }
            
            .message-content {
                max-width: 90%;
                padding: 12px 16px;
            }
            
            .quick-actions {
                padding: 0 15px 10px;
            }
            
            .quick-action {
                font-size: 0.8em;
                padding: 8px 12px;
            }
        }

        /* Scrollbar Styling */
        .chat-messages::-webkit-scrollbar,
        .sidebar::-webkit-scrollbar,
        .feature-panel::-webkit-scrollbar {
            width: 6px;
        }

        .chat-messages::-webkit-scrollbar-track,
        .sidebar::-webkit-scrollbar-track,
        .feature-panel::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        .chat-messages::-webkit-scrollbar-thumb,
        .sidebar::-webkit-scrollbar-thumb,
        .feature-panel::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 3px;
        }

        .chat-messages::-webkit-scrollbar-thumb:hover,
        .sidebar::-webkit-scrollbar-thumb:hover,
        .feature-panel::-webkit-scrollbar-thumb:hover {
            background: #a8a8a8;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Sidebar Navigation -->
        <div class="sidebar">
            <div class="logo">
                <i class="fas fa-hotel"></i>
                <h1>AI Hotel Assistant</h1>
            </div>
            
            <div class="nav-section">
                <h3>Main</h3>
                <div class="nav-item active">
                    <i class="fas fa-comments"></i>
                    <span>Chat Assistant</span>
                </div>
                <div class="nav-item">
                    <i class="fas fa-calendar-check"></i>
                    <span>Reservations</span>
                </div>
                <div class="nav-item">
                    <i class="fas fa-concierge-bell"></i>
                    <span>Services</span>
                </div>
            </div>
            
            <div class="nav-section">
                <h3>Analysis</h3>
                <div class="nav-item">
                    <i class="fas fa-chart-line"></i>
                    <span>Booking Analytics</span>
                </div>
                <div class="nav-item">
                    <i class="fas fa-camera"></i>
                    <span>Room Inspection</span>
                </div>
                <div class="nav-item">
                    <i class="fas fa-comment-dots"></i>
                    <span>Sentiment Analysis</span>
                </div>
            </div>
            
            <div class="stats">
                <div class="stat-item">
                    <span class="stat-label">Occupancy Rate</span>
                    <span class="stat-value">78%</span>
                </div>
                <div class="stat-item">
                    <span class="stat-label">Avg. Rating</span>
                    <span class="stat-value">4.2/5</span>
                </div>
                <div class="stat-item">
                    <span class="stat-label">Response Time</span>
                    <span class="stat-value">2.3s</span>
                </div>
            </div>
        </div>
        
        <!-- Main Content Area -->
        <div class="main-content">
            <div class="header">
                <h2>AI Reservation Assistant</h2>
                <div class="user-info">
                    <span>Welcome, Hotel Manager</span>
                    <div class="user-avatar">HM</div>
                </div>
            </div>
            
            <div class="chat-area">
                <div class="chat-tabs">
                    <div class="chat-tab active">Guest Communication</div>
                    <div class="chat-tab">Room Inspection</div>
                    <div class="chat-tab">Sentiment Analysis</div>
                </div>
                
                <div class="chat-messages" id="chatMessages">
                    <!-- Messages will be inserted here by JavaScript -->
                </div>
                
                <div class="typing-indicator" id="typingIndicator">
                    <div class="typing-dots">
                        <div class="typing-dot"></div>
                        <div class="typing-dot"></div>
                        <div class="typing-dot"></div>
                    </div>
                    <span>AI Assistant is thinking...</span>
                </div>
                
                <div class="quick-actions" id="quickActions">
                    <div class="quick-action" onclick="sendQuickMessage('I want to book a room for 2 adults')">
                        <i class="fas fa-bed"></i>
                        <span>Book a Room</span>
                    </div>
                    <div class="quick-action" onclick="sendQuickMessage('What are your room prices?')">
                        <i class="fas fa-tag"></i>
                        <span>Check Prices</span>
                    </div>
                    <div class="quick-action" onclick="sendQuickMessage('I need to cancel my booking')">
                        <i class="fas fa-times-circle"></i>
                        <span>Cancel Booking</span>
                    </div>
                    <div class="quick-action" onclick="sendQuickMessage('Recommend a room type for a family')">
                        <i class="fas fa-star"></i>
                        <span>Get Recommendations</span>
                    </div>
                </div>
                
                <div class="chat-input-container">
                    <input type="text" class="chat-input" id="chatInput" placeholder="Type your message here..." onkeypress="handleKeyPress(event)">
                    <button class="action-button" id="sendButton" onclick="sendMessage()">
                        <i class="fas fa-paper-plane"></i>
                        <span>Send</span>
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Feature Panel -->
        <div class="feature-panel">
            <div class="feature-header">
                <h3>AI Analysis Tools</h3>
                <p>Advanced features for hotel management</p>
            </div>
            
            <div class="feature-content">
                <div class="feature-section">
                    <h4>Room Quality Inspection</h4>
                    <div class="upload-area" id="uploadArea" onclick="document.getElementById('fileInput').click()">
                        <i class="fas fa-cloud-upload-alt"></i>
                        <div class="upload-text">Upload Room Photo</div>
                        <div class="upload-subtext">Click or drag image to analyze room quality</div>
                    </div>
                    <input type="file" id="fileInput" accept="image/*" style="display: none;" onchange="handleImageUpload(event)">
                    
                    <div class="inspection-results" id="inspectionResults" style="display: none;">
                        <div class="inspection-item">
                            <span class="inspection-label">Cleanliness</span>
                            <span class="inspection-score score-excellent">Excellent</span>
                        </div>
                        <div class="inspection-item">
                            <span class="inspection-label">Organization</span>
                            <span class="inspection-score score-good">Good</span>
                        </div>
                        <div class="inspection-item">
                            <span class="inspection-label">Amenities</span>
                            <span class="inspection-score score-fair">Fair</span>
                        </div>
                        <div class="inspection-item">
                            <span class="inspection-label">Overall Score</span>
                            <span class="inspection-score score-good">8.2/10</span>
                        </div>
                    </div>
                </div>
                
                <div class="feature-section">
                    <h4>Sentiment Analysis</h4>
                    <div class="sentiment-display">
                        <div class="sentiment-icon sentiment-positive">
                            <i class="fas fa-smile"></i>
                        </div>
                        <div class="sentiment-details">
                            <div class="sentiment-label">Positive Sentiment</div>
                            <div class="sentiment-value">78% of recent guest communications</div>
                        </div>
                    </div>
                </div>
                
                <div class="feature-section">
                    <h4>NLP Processing</h4>
                    <div class="upload-area" onclick="analyzeText()">
                        <i class="fas fa-language"></i>
                        <div class="upload-text">Analyze Guest Feedback</div>
                        <div class="upload-subtext">Paste guest comments for sentiment analysis</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Enhanced AI Hotel Chatbot with NLP and Computer Vision
        class EnhancedHotelChatbot {
            constructor() {
                this.conversationHistory = [];
                this.currentContext = {};
                this.userProfile = {};
                this.nlpModel = new NLPAnalyzer();
                this.visionModel = new ComputerVision();
            }

            analyzeBookingPatterns() {
                return {
                    cancellation_rate: 0.32,
                    avg_lead_time: 85,
                    popular_meal_plans: {'Meal Plan 1': 650, 'Not Selected': 280, 'Meal Plan 2': 70},
                    room_type_popularity: {'Room_Type 1': 800, 'Room_Type 4': 150, 'Room_Type 6': 50},
                    peak_months: {10: 180, 8: 165, 9: 155},
                    avg_price_by_room: {'Room_Type 1': 95.50, 'Room_Type 4': 125.30, 'Room_Type 6': 185.75}
                };
            }

            // Enhanced NLP processing
            processWithNLP(userInput) {
                const sentiment = this.nlpModel.analyzeSentiment(userInput);
                const entities = this.nlpModel.extractEntities(userInput);
                const intent = this.nlpModel.classifyIntent(userInput);
                
                return {
                    sentiment,
                    entities,
                    intent,
                    response: this.generateContextualResponse(intent, entities, sentiment)
                };
            }

            // Computer Vision integration for room inspection
            analyzeRoomImage(imageData) {
                return this.visionModel.analyzeRoom(imageData);
            }

            // Main message processing with enhanced NLP
            processMessage(userInput) {
                this.conversationHistory.push({
                    user: userInput,
                    timestamp: new Date().toISOString()
                });

                // Process with NLP
                const nlpResult = this.processWithNLP(userInput);
                
                // Update context
                this.currentContext = {...this.currentContext, ...nlpResult.entities};
                
                // Generate response based on NLP analysis
                let responses = nlpResult.response;
                
                // Add sentiment-aware responses
                if (nlpResult.sentiment.score < 0.3) {
                    responses.unshift("I notice you might have some concerns. Let me help address them.");
                } else if (nlpResult.sentiment.score > 0.7) {
                    responses.unshift("Great to hear your enthusiasm!");
                }
                
                return responses;
            }

            generateContextualResponse(intent, entities, sentiment) {
                const patterns = this.analyzeBookingPatterns();
                const responses = [];
                
                switch(intent) {
                    case 'booking':
                        if (!entities.adults && !entities.nights) {
                            responses.push("I'd love to help you make a booking! Could you tell me:");
                            responses.push("- How many adults and children?");
                            responses.push("- Preferred dates or duration of stay?");
                            responses.push("- Any room type preferences?");
                        } else {
                            responses.push("Great! Here's your booking summary:");
                            if (entities.adults) responses.push(`- Guests: ${entities.adults} adults`);
                            if (entities.nights) responses.push(`- Stay: ${entities.nights} nights`);
                            if (entities.room_type) responses.push(`- Room type: ${entities.room_type}`);
                            
                            const riskScore = this.predictCancellationRisk(entities);
                            responses.push(`- Estimated cancellation risk: ${(riskScore * 100).toFixed(1)}%`);
                            
                            const recommendations = this.getRecommendations(entities);
                            responses.push("Recommendations:");
                            recommendations.forEach(rec => responses.push(`- ${rec}`));
                        }
                        break;
                        
                    case 'cancellation':
                        if (entities.booking_id) {
                            responses.push(`I can help you cancel booking ${entities.booking_id}.`);
                            responses.push("Please confirm you'd like to proceed with cancellation.");
                        } else {
                            responses.push("To help with cancellation, please provide your booking ID (format: INN00001).");
                        }
                        break;
                        
                    case 'recommendation':
                        const recommendations = this.getRecommendations(entities);
                        responses.push("Based on your preferences, here are my recommendations:");
                        recommendations.forEach(rec => responses.push(`- ${rec}`));
                        break;
                        
                    case 'inquiry':
                        if (entities.price || entities.cost) {
                            responses.push("Here are our average room prices:");
                            Object.entries(patterns.avg_price_by_room).forEach(([room, price]) => {
                                responses.push(`- ${room}: $${price.toFixed(2)} per night`);
                            });
                        } else if (entities.room || entities.available) {
                            responses.push("Most popular room types:");
                            Object.entries(patterns.room_type_popularity).forEach(([room, count]) => {
                                responses.push(`- ${room}: ${count} bookings`);
                            });
                        } else if (entities.meal) {
                            responses.push("Our meal plan options:");
                            Object.entries(patterns.popular_meal_plans).forEach(([meal, count]) => {
                                responses.push(`- ${meal}: ${count} guests`);
                            });
                        } else {
                            responses.push("I can help you with:");
                            responses.push("- Booking new reservations");
                            responses.push("- Canceling existing bookings");
                            responses.push("- Room recommendations");
                            responses.push("- Price and availability information");
                        }
                        break;
                        
                    default:
                        responses.push("I'm here to help with your hotel reservation needs!");
                        responses.push("You can ask me about booking, prices, cancellations, or recommendations.");
                }
                
                return responses;
            }

            predictCancellationRisk(bookingDetails) {
                let riskFactors = 0;
                const patterns = this.analyzeBookingPatterns();
                
                if (bookingDetails.lead_time > patterns.avg_lead_time) riskFactors += 1;
                if (bookingDetails.weekend_nights > 2) riskFactors += 1;
                if (bookingDetails.special_requests === 0) riskFactors += 0.5;
                if (bookingDetails.market_segment === 'Online') riskFactors += 0.5;
                
                return Math.min(riskFactors / 3, 1.0);
            }

            getRecommendations(userPreferences) {
                const patterns = this.analyzeBookingPatterns();
                const recommendations = [];
                
                if (userPreferences.room_type) {
                    recommendations.push(`Most popular ${userPreferences.room_type} has ${patterns.room_type_popularity[userPreferences.room_type] || 0} bookings`);
                } else {
                    recommendations.push(`Most popular room: ${Object.keys(patterns.room_type_popularity)[0]}`);
                }
                
                if (userPreferences.meal_plan) {
                    recommendations.push(`${userPreferences.meal_plan} chosen by ${patterns.popular_meal_plans[userPreferences.meal_plan] || 0} guests`);
                } else {
                    recommendations.push(`Recommended meal plan: ${Object.keys(patterns.popular_meal_plans)[0]}`);
                }
                
                recommendations.push(`Average cancellation rate: ${(patterns.cancellation_rate * 100).toFixed(1)}%`);
                recommendations.push(`Optimal booking lead time: ${patterns.avg_lead_time.toFixed(0)} days`);
                
                return recommendations;
            }
        }

        // NLP Analyzer Class
        class NLPAnalyzer {
            analyzeSentiment(text) {
                const positiveWords = ['great', 'excellent', 'wonderful', 'amazing', 'good', 'nice', 'love', 'perfect', 'fantastic'];
                const negativeWords = ['terrible', 'awful', 'horrible', 'bad', 'disappointing', 'poor', 'hate', 'worst'];
                
                const words = text.toLowerCase().split(/\s+/);
                let positiveCount = 0;
                let negativeCount = 0;
                
                words.forEach(word => {
                    if (positiveWords.includes(word)) positiveCount++;
                    if (negativeWords.includes(word)) negativeCount++;
                });
                
                const total = positiveCount + negativeCount;
                let score = 0.5; // neutral default
                
                if (total > 0) {
                    score = positiveCount / total;
                }
                
                let label = 'neutral';
                if (score > 0.7) label = 'positive';
                else if (score < 0.3) label = 'negative';
                
                return { score, label, positiveCount, negativeCount };
            }
            
            extractEntities(text) {
                const entities = {};
                const textLower = text.toLowerCase();
                
                // Extract adults
                const adultMatch = textLower.match(/(\d+)\s*(adults?|people)/);
                if (adultMatch) entities.adults = parseInt(adultMatch[1]);
                
                // Extract nights
                const nightMatch = textLower.match(/(\d+)\s*(nights?|days?)/);
                if (nightMatch) entities.nights = parseInt(nightMatch[1]);
                
                // Extract room type
                if (textLower.includes('suite') || textLower.includes('luxury')) entities.room_type = 'Room_Type 6';
                else if (textLower.includes('deluxe')) entities.room_type = 'Room_Type 4';
                else if (textLower.includes('standard') || textLower.includes('economy')) entities.room_type = 'Room_Type 1';
                
                // Extract meal plan
                if (textLower.includes('meal plan 1') || textLower.includes('breakfast')) entities.meal_plan = 'Meal Plan 1';
                else if (textLower.includes('meal plan 2')) entities.meal_plan = 'Meal Plan 2';
                
                // Extract booking ID
                const bookingMatch = textLower.match(/inn\d+/);
                if (bookingMatch) entities.booking_id = bookingMatch[0].toUpperCase();
                
                // Extract price/cost keywords
                if (textLower.includes('price') || textLower.includes('cost')) entities.price = true;
                
                // Extract room/availability keywords
                if (textLower.includes('room') || textLower.includes('available')) entities.room = true;
                
                // Extract meal keywords
                if (textLower.includes('meal') || textLower.includes('food')) entities.meal = true;
                
                return entities;
            }
            
            classifyIntent(text) {
                const textLower = text.toLowerCase();
                const intents = {
                    'booking': ['book', 'reserve', 'make a reservation', 'new booking'],
                    'cancellation': ['cancel', 'cancellation'],
                    'modification': ['modify', 'change', 'update'],
                    'recommendation': ['recommend', 'suggest', 'what should', 'which is best'],
                    'inquiry': ['price', 'cost', 'available', 'room', 'meal', 'help', 'information', 'what']
                };
                
                for (const [intent, keywords] of Object.entries(intents)) {
                    if (keywords.some(keyword => textLower.includes(keyword))) {
                        return intent;
                    }
                }
                return 'inquiry';
            }
        }

        // Computer Vision Class for Room Inspection
        class ComputerVision {
            analyzeRoom(imageData) {
                // Simulate AI analysis of room image
                // In a real implementation, this would connect to a computer vision API
                
                // Simulate processing delay
                return new Promise((resolve) => {
                    setTimeout(() => {
                        const scores = {
                            cleanliness: this.getRandomScore(0.7, 0.95),
                            organization: this.getRandomScore(0.6, 0.9),
                            amenities: this.getRandomScore(0.5, 0.85),
                            lighting: this.getRandomScore(0.7, 0.9),
                            overall: this.getRandomScore(0.65, 0.9)
                        };
                        
                        const results = {
                            scores,
                            feedback: this.generateFeedback(scores),
                            issues: this.detectIssues(scores)
                        };
                        
                        resolve(results);
                    }, 2000);
                });
            }
            
            getRandomScore(min, max) {
                return Math.random() * (max - min) + min;
            }
            
            generateFeedback(scores) {
                const feedback = [];
                
                if (scores.cleanliness > 0.9) {
                    feedback.push("Room is exceptionally clean and well-maintained.");
                } else if (scores.cleanliness > 0.7) {
                    feedback.push("Room meets cleanliness standards with minor areas for improvement.");
                } else {
                    feedback.push("Room requires attention to cleanliness standards.");
                }
                
                if (scores.organization > 0.8) {
                    feedback.push("Excellent organization and layout.");
                } else if (scores.organization > 0.6) {
                    feedback.push("Room organization is adequate.");
                } else {
                    feedback.push("Room organization needs improvement.");
                }
                
                return feedback;
            }
            
            detectIssues(scores) {
                const issues = [];
                
                if (scores.cleanliness < 0.7) {
                    issues.push("Cleanliness below standard");
                }
                
                if (scores.organization < 0.6) {
                    issues.push("Poor organization");
                }
                
                if (scores.amenities < 0.6) {
                    issues.push("Missing or inadequate amenities");
                }
                
                return issues;
            }
        }

        // DOM Manipulation and UI Logic
        const chatbot = new EnhancedHotelChatbot();
        const chatMessages = document.getElementById('chatMessages');
        const chatInput = document.getElementById('chatInput');
        const sendButton = document.getElementById('sendButton');
        const typingIndicator = document.getElementById('typingIndicator');
        const uploadArea = document.getElementById('uploadArea');
        const inspectionResults = document.getElementById('inspectionResults');

        // Add welcome message
        addMessage('bot', [
            "Hello! I'm your enhanced AI Hotel Assistant 🏨",
            "I now feature:",
            "• Advanced NLP for better understanding",
            "• Sentiment analysis of your messages", 
            "• Computer vision for room inspection",
            "• Intelligent booking recommendations",
            "How can I assist with your hotel needs today?"
        ]);

        function addMessage(sender, content) {
            const messageDiv = document.createElement('div');
            messageDiv.className = `message ${sender}`;
            
            const avatar = document.createElement('div');
            avatar.className = 'avatar';
            avatar.textContent = sender === 'user' ? '👤' : '🤖';
            
            const messageContent = document.createElement('div');
            messageContent.className = 'message-content';
            
            if (Array.isArray(content)) {
                content.forEach(line => {
                    const p = document.createElement('p');
                    p.textContent = line;
                    p.style.margin = '2px 0';
                    messageContent.appendChild(p);
                });
            } else {
                messageContent.textContent = content;
            }
            
            const messageTime = document.createElement('div');
            messageTime.className = 'message-time';
            messageTime.textContent = new Date().toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
            
            messageContent.appendChild(messageTime);
            messageDiv.appendChild(avatar);
            messageDiv.appendChild(messageContent);
            chatMessages.appendChild(messageDiv);
            
            // Scroll to bottom
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }

        function showTypingIndicator() {
            typingIndicator.style.display = 'flex';
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }

        function hideTypingIndicator() {
            typingIndicator.style.display = 'none';
        }

        function sendMessage() {
            const message = chatInput.value.trim();
            if (!message) return;

            // Add user message
            addMessage('user', message);
            chatInput.value = '';
            
            // Disable input while processing
            chatInput.disabled = true;
            sendButton.disabled = true;
            
            // Show typing indicator
            showTypingIndicator();
            
            // Simulate AI processing delay
            setTimeout(() => {
                hideTypingIndicator();
                
                // Process message with enhanced AI
                const responses = chatbot.processMessage(message);
                addMessage('bot', responses);
                
                // Re-enable input
                chatInput.disabled = false;
                sendButton.disabled = false;
                chatInput.focus();
            }, 1500 + Math.random() * 1000);
        }

        function sendQuickMessage(message) {
            chatInput.value = message;
            sendMessage();
        }

        function handleKeyPress(event) {
            if (event.key === 'Enter') {
                sendMessage();
            }
        }

        // Computer Vision Integration
        function handleImageUpload(event) {
            const file = event.target.files[0];
            if (!file) return;
            
            // Show loading state
            uploadArea.innerHTML = `
                <i class="fas fa-spinner fa-spin"></i>
                <div class="upload-text">Analyzing Room Image...</div>
                <div class="upload-subtext">AI is inspecting room quality</div>
            `;
            
            // Simulate computer vision analysis
            setTimeout(() => {
                // Show results
                inspectionResults.style.display = 'block';
                
                // Reset upload area
                uploadArea.innerHTML = `
                    <i class="fas fa-check-circle" style="color: #2ecc71;"></i>
                    <div class="upload-text">Analysis Complete</div>
                    <div class="upload-subtext">Click to analyze another room</div>
                `;
                
                // In a real implementation, we would display actual analysis results
            }, 3000);
        }

        function analyzeText() {
            const text = prompt("Paste guest feedback for sentiment analysis:");
            if (text) {
                const sentiment = chatbot.nlpModel.analyzeSentiment(text);
                alert(`Sentiment Analysis Result:\n\nScore: ${(sentiment.score * 100).toFixed(1)}%\nLabel: ${sentiment.label}\nPositive words: ${sentiment.positiveCount}\nNegative words: ${sentiment.negativeCount}`);
            }
        }

        // Initialize drag and drop for image upload
        uploadArea.addEventListener('dragover', (e) => {
            e.preventDefault();
            uploadArea.style.borderColor = '#3498db';
            uploadArea.style.background = '#f8f9fa';
        });

        uploadArea.addEventListener('dragleave', () => {
            uploadArea.style.borderColor = '#bdc3c7';
            uploadArea.style.background = 'transparent';
        });

        uploadArea.addEventListener('drop', (e) => {
            e.preventDefault();
            uploadArea.style.borderColor = '#bdc3c7';
            uploadArea.style.background = 'transparent';
            
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                const fileInput = document.getElementById('fileInput');
                fileInput.files = files;
                handleImageUpload({ target: fileInput });
            }
        });

        // Focus input on load
        chatInput.focus();
    </script>
</body>
</html>