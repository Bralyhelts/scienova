const express = require('express');
const path = require('path');
const fs = require('fs');
const multer = require('multer');
const cors = require('cors');

const app = express();
const PORT = 3000;

// Setup storage for uploads
const uploadDir = path.join(__dirname, 'uploads');
if (!fs.existsSync(uploadDir)) fs.mkdirSync(uploadDir);

const storage = multer.diskStorage({
  destination: (req, file, cb) => cb(null, uploadDir),
  filename: (req, file, cb) => cb(null, Date.now() + '-' + file.originalname)
});
const upload = multer({ storage });

// Middleware
app.use(cors());
app.use(express.json());
app.use(express.urlencoded({ extended: true }));
app.use(express.static(__dirname)); // serves your index.html, admin.html, style.css, script.js
app.use('/uploads', express.static(uploadDir));

// Fake Database (use JSON files)
let resources = {
  Biology: ["Cell Biology Notes","Genetics Revision Guide","A-Level Biology Past Paper Pack"],
  Chemistry: ["Organic Chemistry Summary","Mole Concept Worked Examples","Practical Chemistry Guide"],
  Mathematics: ["Pure Mathematics Formula Sheet","Calculus Worked Examples","Statistics Revision Pack"],
  Physics: ["Mechanics Notes","Electricity & Circuits Guide","Waves and Optics Revision"]
};
let reviews = [
  { name: "Amara N.", role: "Senior Six", text: "The subject organization makes revision much easier. I can find exactly what I need." },
  { name: "Michael K.", role: "Senior Five", text: "The worked mathematics resources have helped me understand topics I used to avoid." }
];
let users = [];

// === BUTTON FUNCTIONALITY ===

// 1. Button: View resources -> openResource('Biology') button
app.get('/api/resources/:subject', (req, res) => {
  const subject = req.params.subject;
  res.json({ subject, files: resources[subject] || [] });
});

// 2. Button: Add review -> + Add review button
app.post('/api/reviews', (req, res) => {
  const { name, text } = req.body;
  if(!name ||!text) return res.json({ success: false });
  reviews.unshift({ name, role: "New member", text });
  res.json({ success: true, reviews });
});
app.get('/api/reviews', (req, res) => res.json(reviews));

// 3. Button: Login / Signup -> Log in button
app.post('/api/login', (req, res) => {
  const { email, password } = req.body;
  const user = users.find(u => u.email === email);
  if(user) {
    res.json({ success: true, message: "Login successful", user });
  } else {
    // Auto-create for demo
    const newUser = { id: Date.now(), email, password };
    users.push(newUser);
    res.json({ success: true, message: "Account created & logged in", user: newUser });
  }
});

// 4. Button: Admin Upload -> Upload resource button in admin.html
app.post('/api/upload', upload.single('file'), (req, res) => {
  const { title, subject } = req.body;
  if (!resources[subject]) resources[subject] = [];
  resources[subject].push(title + ` (${req.file.filename})`);
  res.json({ success: true, message: `Uploaded: ${title} to ${subject}` });
});

// Home
app.get('/', (req, res) => res.sendFile(path.join(__dirname, 'index.html')));

app.listen(PORT, () => {
  console.log(`✅ SCIENOVA running at http://localhost:${PORT}`);
  console.log(`✅ Admin at http://localhost:${PORT}/admin.html`);
});
