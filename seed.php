<?php
// seed.php - Run once to populate fake data
include 'db_connect.php';

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// 1. Insert Fake Users
$users = [
    ['full_name' => 'Aarav Sharma', 'email' => 'aarav@example.com', 'pass' => password_hash('password123', PASSWORD_DEFAULT)],
    ['full_name' => 'Priya Patel', 'email' => 'priya@example.com', 'pass' => password_hash('password123', PASSWORD_DEFAULT)],
    ['full_name' => 'Rahul Singh', 'email' => 'rahul@example.com', 'pass' => password_hash('password123', PASSWORD_DEFAULT)],
    ['full_name' => 'Anjali Desai', 'email' => 'anjali@example.com', 'pass' => password_hash('password123', PASSWORD_DEFAULT)],
    ['full_name' => 'Vikram Reddy', 'email' => 'vikram@example.com', 'pass' => password_hash('password123', PASSWORD_DEFAULT)]
];

$user_ids = [];
foreach ($users as $u) {
    // Check if exists
    $check = $conn->query("SELECT id FROM users WHERE email='{$u['email']}'");
    if ($check->num_rows == 0) {
        $conn->query("INSERT INTO users (full_name, email, password) VALUES ('{$u['full_name']}', '{$u['email']}', '{$u['pass']}')");
        $user_ids[$u['full_name']] = $conn->insert_id;
    } else {
        $user_ids[$u['full_name']] = $check->fetch_assoc()['id'];
    }
}

// 2. Fake Complaints Data
$complaints_data = [
    // Aarav (Gold - 12 resolved)
    ...array_fill(0, 12, ['user' => 'Aarav Sharma', 'title' => 'Deep Pothole on MG Road', 'cat' => 'Road', 'desc' => 'There is a massive pothole causing severe traffic slowdowns near the metro station.', 'loc' => 'MG Road, Bengaluru', 'status' => 'Resolved', 'img' => 'https://images.unsplash.com/photo-1515162816999-a0c47dc192f7?auto=format&fit=crop&q=80&w=400']),
    
    // Priya (Silver - 6 resolved)
    ...array_fill(0, 6, ['user' => 'Priya Patel', 'title' => 'Water Pipe Burst in Indiranagar', 'cat' => 'Water', 'desc' => 'A main water line has burst and is flooding the street.', 'loc' => '100ft Road, Indiranagar, Bengaluru', 'status' => 'Resolved', 'img' => 'https://images.unsplash.com/photo-1541888086225-b6d8b68cb263?auto=format&fit=crop&q=80&w=400']),
    
    // Rahul (Bronze - 2 resolved, 1 pending)
    ['user' => 'Rahul Singh', 'title' => 'Streetlight not working', 'cat' => 'Electricity', 'desc' => 'The streetlight outside my house has been broken for 2 weeks.', 'loc' => 'Koramangala 4th Block, Bengaluru', 'status' => 'Resolved', 'img' => 'https://images.unsplash.com/photo-1516997424682-15948332a677?auto=format&fit=crop&q=80&w=400'],
    ['user' => 'Rahul Singh', 'title' => 'Garbage Dump Overflow', 'cat' => 'Garbage', 'desc' => 'The local garbage bin is overflowing onto the sidewalk.', 'loc' => 'Jayanagar 4th Block, Bengaluru', 'status' => 'Resolved', 'img' => 'https://images.unsplash.com/photo-1530587191325-3db32d826c18?auto=format&fit=crop&q=80&w=400'],
    ['user' => 'Rahul Singh', 'title' => 'Fallen Tree Branch', 'cat' => 'Road', 'desc' => 'A large tree branch fell during the storm.', 'loc' => 'JP Nagar 2nd Phase, Bengaluru', 'status' => 'Pending', 'img' => ''],
    
    // Anjali (Silver - 5 resolved)
    ...array_fill(0, 5, ['user' => 'Anjali Desai', 'title' => 'Open Manhole', 'cat' => 'Road', 'desc' => 'Dangerous open manhole in the middle of the walking path.', 'loc' => 'HSR Layout Sector 2, Bengaluru', 'status' => 'Resolved', 'img' => '']),
    
    // Vikram (Bronze - 1 resolved)
    ['user' => 'Vikram Reddy', 'title' => 'Frequent Power Cuts', 'cat' => 'Electricity', 'desc' => 'Experiencing daily power cuts for 3-4 hours.', 'loc' => 'Whitefield, Bengaluru', 'status' => 'Resolved', 'img' => '']
];

foreach ($complaints_data as $i => $c) {
    $uid = $user_ids[$c['user']];
    // Slightly randomize the titles so they aren't identical in the DB
    $title = $conn->real_escape_string($c['title'] . ($i > 0 ? " #" . $i : ""));
    $desc = $conn->real_escape_string($c['desc']);
    $loc = $conn->real_escape_string($c['loc']);
    
    $conn->query("INSERT INTO complaints (user_id, title, category, description, location, attachment, status, created_at) 
                  VALUES ($uid, '$title', '{$c['cat']}', '$desc', '$loc', '{$c['img']}', '{$c['status']}', NOW() - INTERVAL ".rand(1,30)." DAY)");
}

echo "Database seeded successfully with AI generated data!\n";
?>
