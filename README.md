# Business_Analysis
School Project. Student Portfolio

# AI Solution 
Shortage of staff impacts the quality of service, reviews, manual check in delay. AI can help by taking on responsibilities. For example, it could manage reservation changes, update guest profiles, or even schedule housekeeping tasks base on real-time room availability, by offloading these adminsistrative burdens. Use of biometric system to be implimented for customers'  possessions to be secured or safe. Use AI to help customize suites in accordance to the customer's needs or preferences.

# Business Objectives
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

# Machine Learning Approach
Reservation Management and Guest Customization
Machine Learning Approach:
- Natural Language Processing (NLP): To respond to guest inquiries via chatbots and email effectively reducing the front-desk workload and providing faster services. NLP involves both Natural Language Understanding (NLU) to interpret guest requests and Natural Language Generation (NLG) to formulate automated responses. NLP techniques like Tokenization (breaking text into simple units), Stemming (shortening words to their root form), and Lemmatization (finding the base form of words) are essential for processing guest queries.
- Supervised Learning - Classification: To customize suites based on customer needs or preferences. By analyzing past booking data and preferences, classification algorithms can predict the ideal suite type or amenities for a guest. Algorithms like *Decision Tree, Random Forest, or Logistic Regression* are suitable for predicting categorized outputs, such as preferred room features or package types.

Guest Profile Updates and Personalized Services
Machine Learning Approach:
- Unsupervised Learning - Clustering: To discover inherent groupings or segments among guests based on their purchasing behavior, demographics, or stated preferences. K-Means Clustering can group similar guests, allowing the hotel to make personalized suggestions and offers, enhancing the client's experience.
- Supervised Learning - Classification: To predict guest loyalty status or the likelihood of repeat bookings. By learning from labeled data of past guests, algorithms like *Logistic Regression, Support Vector Machine (SVM), Naïve Bayes, or K-Nearest Neighbors (KNN)* can classify guests into different loyalty tiers and predict their propensity to return.

Housekeeping Scheduling
-Machine Learning Approach:
- Supervised Learning - Regression: To estimate the time required for cleaning various room types based on the length of the previous guest's stay. *Linear Regression or Random Forest* can predict these continuous values. This enables more accurate and efficient scheduling, allowing staff to know their schedules and plan ahead.

Automated Check-in/Check-out and Security
Machine Learning Approach:
- Computer Vision: A tool to support and make it easier for guests to check in using ID verification or facial recognition. Computer Vision processes images to extract, analyze, and understand useful information, simulating the human visual system through *machine learning and deep learning algorithms*. This is crucial for speeding up check-in processes.
- Pattern Recognition (within ML/Deep Learning): The implementation of a biometric system, like fingerprint registration for secure access, relies on pattern recognition algorithms for accurate and reliable verification.

Operational Insights and Performance Monitoring
Machine Learning Approach:
- Time Series Analysis: To predict future occupancy rates and revenue growth, which are key business objectives. Time series data is sequential, and sequence analysis predicts future events based on past observations. A Hidden Markov Model (HMM) is suitable for analyzing time series data and predicting future states.
- Sentiment Analysis Tools (part of NLP): To monitor online reviews and guest feedback, detecting service issues and contributing to guest satisfaction ratings. This involves classification algorithms that categorize feedback into sentiment categories (e.g. positive, negative, neutral) or identify specific problem areas.

# Evaluation for Accuracy of the AI Models in AI Solution
Reservation Management and Guest Customization:
- For customizing suites based on customer needs or preferences, which uses supervised learning classification algorithms, the performance would be evaluated using metrics such as a Confusion Matrix, Accuracy, Precision, or Recall/Sensitivity.
- For responding to guest inquiries via chatbots and email using Natural Language Processing (NLP), the objective is to reduce front-desk workload and provide faster services. The effectiveness will be measured by the accuracy of understanding guest requests (Natural Language Understanding - NLU) and formulating appropriate responses (Natural Language Generation - NLG).

Guest Profile Updates and Personalized Services:
- For predicting guest loyalty status or the likelihood of repeat bookings, which also uses supervised learning classification algorithms, the evaluation will again involve Confusion Matrix, Accuracy, Precision, or Recall/Sensitivity.

Housekeeping Scheduling:
- To estimate the time required for cleaning various room types based on previous guest stays, using supervised learning regression algorithms, the model's accuracy will be evaluated by considering Variance, Bias, Error, and Accuracy.

Automated Check-in/Check-out and Security:
- The implementation of a biometric system for secure access relies on pattern recognition algorithms for accurate and reliable verification. Potential risks include errors in biometric access, data misuse, or system failures.

Operational Insights and Performance Monitoring:
- Sentiment Analysis Tools (part of NLP) monitor online reviews and guest feedback, using classification algorithms to categorize feedback (positive, negative, neutral). The accuracy of this categorization would be evaluated using Confusion Matrix, Accuracy, Precision, or Recall/Sensitivity.
- Time Series Analysis is used to predict future occupancy rates and revenue growth. For this, accuracy would involve how close the predictions are to the actual future outcomes. The evaluation would likely focus on the difference between predicted and actual values.
