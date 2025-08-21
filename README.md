# Business_Analysis
School Project. Student Portfolio

AI Solution : Shortage of staff impacts the quality of service, reviews, manual check in delay. AI can help by taking on responsibilities. For example, it could manage reservation changes, update guest profiles, or even schedule housekeeping tasks base on real-time room availability, by offloading these adminsistrative burdens. Use of biometric system to be implimented for customers'  possessions to be secured or safe. Use AI to help customize suites in accordance to the customer's needs or preferences.

Business Objectives
- High Occupancy Rates: Keep a high percentage of booked rooms throughout the year.
- Increase Guest Satisfaction: Deliver good customer service to boost reviews, referrals and repeat bookings.
- Improve Operational Efficiency: Reduce costs without compromising service quality through technology and staff training.
- Strengthen Brand Reputation: Build trust and loyalty through consistent quality, environmental responsibility, and community involvement.
- Promote Sustainability: Implement eco-friendly practices like waste reduction, energy conservation and local sourcing.

Business Success Criteria
- Occupancy rate of over 75% year-round.
- Guest satisfaction rating of 4.5-5 on review platforms.
- Revenue growth of at least 10% annually.
- Operational cost savings of around 5–10% per year through efficiency measures.
- Repeat guest rate of over 30%.

# Business Background
Hotel is an important component of the hospitality industry, providing paid accommodation, food and and other services to guests. They cater to a group of diverse individuals, including tourists, business guest and local guests. The business environment is influenced by economic conditions, travel trends and consumer technology, such as online booking platforms and review websites. The focus is to create a positive guest experience while managing operations to ensure profitability.

# Requirements
To ensure the successful implementation of the AI hotel management system, several key functional and non-functional requirements must be met.
 Functional Requirements:
- Reservation Management: AI must process changes, cancellations, and upgrades. Customize room or suits to customers satisfction.
- Guest Profile Updates: Automatically maintain guest history, preferences, and loyalty status. 
- Housekeeping Scheduling: Assign cleaning tasks based on real-time room availability and guest status. Use AI system to book or asign tasks to the staff to do the services needed in favor of the customer's preferences.
- Automated Check-in/Check-out: Enable self-service through kiosks or mobile applications. Use of biometric system prints to be registered for safety access to the allocated room(s). The system to be functional until the clocking or signing out of the guest(s). 
- Operational Insights: Generate real-time reports on occupancy, service status, and guest feedback.
- 
 Non-Functional Requirements:
- Reliability: System must operate 24/7 with minimal downtime.
- Data Security: Ensure guest data is protected in compliance with privacy regulations (e.g., GDPR).
- User-Friendly Design: Easy-to-navigate interfaces for staff and guests.
- Scalability: Able to support expansion across multiple properties or hotel chains.
- Multi-language Support: Cater to international guests through localized experiences, AI languagee translation for guests to use their suited or prefered language and automated responses translated for better and fast services.

# Constraints
The project must be designed and implemented within the following limitations:
- Budget Constraints: Financial resources may restrict the scope or speed of implementation.
- Legacy Systems: Existing hotel software may limit integration or require upgrades.
- Regulatory Compliance: Must adhere to local labor laws and international data privacy standards.

# Risks
Potential risks that could impact the success of the AI solution include:

Technical Risks:
- AI errors in managing bookings or misinterpreting guest requests, data misuse or theft and unautharized access. Personal data theft leading to customer complaints and unsatisfactory.
- System failures or bugs are causing delays in check-ins or room assignments. Error to biometrics access.
- Integration difficulties with outdated or proprietary systems, staff inexperienced to the new AI tool or system. staff difficulties with time management.

 Operational Risks:
- Resistance from staff unwilling to adopt new technologies.
- Loss of the personal touch that guests expect in hospitality.
- Misuse or underutilization of the system due to insufficient training.

 Security & Compliance Risks:
- Data breaches or leaks of personal guest information.
- Legal action due to non-compliance with data protection laws.

 Reputational Risks:
- Negative guest experiences caused by AI mistakes (e.g., wrong room, poor service, untidyness , wrong schedule error in biometric system and staff allocation tasks).
  
# Initial Assessment of Tools and Techniques
AI Tools and Techniques:
-Natural Language Processing : Responds to guest inquiries via chatbot or email. It reduces front-desk workload
-Robotic Process Automation : Automates repetitive tasks(sending confirmations) , reduces manual administrative work and errors
-Computer Vision : Supports and makes it easier for guests to check in using ID verification or facial recognition
-Sentiment Analysis Tools : Monitors Online reviews and guest feedback to detect service issues

Intergration Tools:
-Cloud Platforms(e.g Azure) : Used to host the AI application and ensuring that the system runs 24/7 in comfort of your own space or client's.

Data Management Tools:
-Database Management Systems(e.g.MySQL) : Can be used to store guest profiles and reservations. keep records and make suggestions best for the client's needs or want.

# Problem definition
What is the problem?:
- Hotels in our region are facing ongoing staff shortages, leading to slower check-ins, delayed housekeeping, and reduced responsiveness to guest requests.
-  This lowers service quality, results in poor online reviews, and decreases repeat bookings, which in turn reduces occupancy rates and revenue.

How relevant is it to the theme?:
- The issue aligns with the theme “AI Solution for Industries” because AI can take over repetitive tasks such as managing reservations, updating guest profiles, scheduling housekeeping, and answering guest queries. It will also speed up the check-in processes.

How beneficial it will be in solving the problem?:
- By solving this problem with AI, hotels can maintain service quality despite limited staff. 
- This will improve guest satisfaction, protect the hotel’s reputation, and increase revenue.
- The local municipality will also benefit from sustained tourism, more visitor spending in local businesses, and stable contributions from tourism-related taxes, helping the community’s economy grow.
- operational effieciency and also support local employment stability
- The AI tool will make queries and offer the best of service so client's can be happy, with that even staff members will know their schedules and plan ahead of time.

Machine Learning Approach
- Data Collection
• Historical guest bookings and preferences
• Service usage logs
• Chat/interactions with our AI tool 
• Real-time feedback (ratings, surveys)
- Model Types
• Recommendation System: Collaborative filtering or content-based filtering to suggest amenities and activities.
• Natural Language Processing (NLP): Intent classification and response generation using models like BERT or GPT-3/4 for chatbots.
• Time Series/Forecasting: Predict peak service times and guest needs using regression or LSTM networks.
• Sentiment Analysis: Classify feedback using supervised learning (SVM, Random Forest, or deep learning).
- Example Pipeline
• Preprocessing: Clean and anonymize guest data.
• Feature Engineering: Extract guest preferences, visit frequency, and service ratings.
• Model Training:
• Recommendation: Train with guest-item interaction matrix.
• NLP: Fine-tune a pre-trained model on hotel-specific queries.
• Sentiment: Train classifier on labeled feedback.
• Integration: Deploy models behind a chatbot or mobile app.
- Implementation Steps
• Define Use Cases: List guest scenarios (check-in, dining, complaints).
• Gather Data: Collaborate with hotel IT to access logs and guest feedback.
• Build MVP: Start with a FAQ chatbot and basic recommendation engine.
• Iterate: Add predictive features and sentiment analysis.
• Deploy & Monitor: Integrate with hotel systems, track guest satisfaction.
- Tech Stack Suggestions
• Python (scikit-learn, TensorFlow, PyTorch)
• NLP: HuggingFace Transformers
• Database: PostgreSQL, MongoDB
• Frontend: Mobile App (React Native/Flutter) or Web Dashboard
 - Sample Use Case
Guest: “Can you show me a friendly usable hotel for a wheelchair person around the area?” AI Tool: [recommends 3 top hotels and shows pictures that are eco-friendly to a wheelchair users]
