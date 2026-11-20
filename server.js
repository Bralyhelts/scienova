const express = require('express');
const fs = require('fs');
const path = require('path');
const multer = require('multer');
const app = express();
app.use(express.json());
app.use(express.static(__dirname));
const DATA = path.join(__dirname, 'data');
const UP = path.join(__dirname, 'uploads');
if (!fs.existsSync(DATA)) fs.mkdirSync(DATA);
if (!fs.existsSync(UP)) fs.mkdirSync(UP);
const load = (f, def) => { try { return JSON.parse(fs.readFileSync(path.join(DATA, f))); } catch(e) { return def; } };
const save = (f, d) => fs.writeFileSync(path.join(DATA, f), JSON.stringify(d, null, 2));
let users = load('users.json', []), payments = load('payments.json', []), papers = load('papers.json', []), wallets = load('wallets.json', {});
const upload = multer({ dest: UP });

app.post('/api/register', (req, res) => {
  const { name, email, phone, password, pin, role } = req.body;
  if (users.find(u => u.phone === phone)) return res.json({ success: false, msg: 'Phone already exists' });
  const u = { name, email, phone, password, pin, role, wallet: 0, createdAt: new Date() };
  users.push(u); wallets[phone] = 0;
  save('users.json', users); save('wallets.json', wallets);
  res.json({ success: true, user: u });
});
app.post('/api/login', (req, res) => {
  const u = users.find(x => x.phone === req.body.phone && x.password === req.body.password);
  res.json({ success: !!u, user: u });
});
app.post('/api/deposit', (req, res) => {
  const { phone, pin, amount, txId, method } = req.body;
  const u = users.find(x => x.phone === phone);
  if (!u) return res.json({ success: false, msg: 'Phone not found' });
  if (u.pin !== pin) return res.json({ success: false, msg: 'Wrong PIN for phone ' + phone });
  wallets[phone] = (wallets[phone] || 0) + Number(amount); u.wallet = wallets[phone];
  payments.push({ phone, amount, txId, method, type: 'deposit', createdAt: new Date() });
  save('users.json', users); save('wallets.json', wallets); save('payments.json', payments);
  res.json({ success: true, balance: wallets[phone] });
});
app.post('/api/buy', (req, res) => {
  const { phone, pin, paperId, price } = req.body;
  const u = users.find(x => x.phone === phone);
  if (!u || u.pin !== pin) return res.json({ success: false, msg: 'Wrong PIN for this phone' });
  if (wallets[phone] < price) return res.json({ success: false, msg: 'Insufficient balance - Deposit first' });
  wallets[phone] -= price; u.wallet = wallets[phone];
  payments.push({ phone, amount: -price, paperId, type: 'purchase', createdAt: new Date() });
  save('users.json', users); save('wallets.json', wallets); save('payments.json', payments);
  res.json({ success: true, balance: wallets[phone] });
});
app.post('/api/contribute', upload.single('file'), (req, res) => {
  const p = { id: Date.now().toString(), title: req.body.title, subject: req.body.subject, level: req.body.level, school: req.body.school, filename: req.file ? req.file.filename : '', views: 0, downloads: 0, status: 'approved', locked: false, adminOnly: false, createdAt: new Date() };
  papers.push(p); save('papers.json', papers); res.json({ success: true, paper: p });
});
app.post('/api/admin-paper', upload.single('file'), (req, res) => {
  const p = { id: 'ADMIN-' + Date.now(), title: req.body.title, subject: req.body.subject, level: req.body.level, author: req.body.author, school: req.body.school, price: req.body.price, desc: req.body.desc, filename: req.file ? req.file.filename : '', views: 0, downloads: 0, status: 'approved', locked: true, adminOnly: true, createdAt: new Date() };
  papers.push(p); save('papers.json', papers); res.json({ success: true, paper: p });
});
app.get('/api/papers', (req, res) => res.json(papers));
app.get('/api/payments', (req, res) => res.json(payments));
app.get('/api/users', (req, res) => res.json(users));
app.get('/api/search-user', (req, res) => { res.json(users.find(x => x.phone === req.query.phone) || null); });
const PORT = process.env.PORT || 10000;
app.listen(PORT, () => console.log('SCIENOVA on ' + PORT));
