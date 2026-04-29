-- updates.sql
USE civic_clarity;

CREATE TABLE IF NOT EXISTS announcements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    theme VARCHAR(50),
    title VARCHAR(200),
    description TEXT,
    borderColor VARCHAR(20),
    colorClass VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO announcements (theme, title, description, borderColor, colorClass) VALUES
('Traffic Alert', 'Main St. Maintenance', 'Scheduled road resurfacing starting next Monday. Expect delays between 8 AM and 4 PM.', 'border-primary', 'text-primary'),
('Community Event', 'Town Hall: Green Spaces', 'Join the discussion about the new park development project at City Hall this Thursday.', 'border-tertiary', 'text-tertiary'),
('Utilities update', 'Water Smart Program', 'New digital meters are being installed in District 4. Check your email for scheduling details.', 'border-secondary', 'text-secondary');

-- Remap dummy images to the actual generated DB ones
UPDATE complaints SET attachment = 'uploads/pothole.png' WHERE title LIKE '%Pothole%';
UPDATE complaints SET attachment = 'uploads/streetlight.png' WHERE title LIKE '%Streetlight%';
UPDATE complaints SET attachment = 'uploads/graffiti.png' WHERE title LIKE '%Graffiti%';
UPDATE complaints SET attachment = 'uploads/pothole.png' WHERE title LIKE '%Water%';
