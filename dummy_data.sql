-- dummy_data.sql
USE civic_clarity;

INSERT INTO users (id, full_name, email, password) VALUES
(1, 'Digital Citizen', 'citizen@example.com', '$2y$10$Q.O/R5Q7T5R8V/tZ6Hn1lOu9v/wzBq.wI1xSXXRzX.4HXY0/I9mG'),
(2, 'Admin Officer', 'admin@example.com', '$2y$10$Q.O/R5Q7T5R8V/tZ6Hn1lOu9v/wzBq.wI1xSXXRzX.4HXY0/I9mG')
ON DUPLICATE KEY UPDATE id=id;

INSERT INTO complaints (user_id, title, category, description, attachment, status) VALUES
(1, 'Pothole on 5th Avenue', 'Road', 'Deep pothole reported 2 days ago near the central intersection.', '', 'Pending'),
(1, 'Streetlight Outage', 'Electricity', 'Park area illumination is currently down, causing safety hazards.', '', 'Pending'),
(1, 'Graffiti Removal', 'Garbage', 'Public bench in Sunnyvale Park needs graffiti cleaning.', '', 'Resolved'),
(1, 'Water Main Leak', 'Water', 'Water flooding the local park area next to the elementary school.', '', 'Pending');
