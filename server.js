const express = require('express');
const path = require('path');
const fs = require('fs');
const cors = require('cors');
const multer = require('multer');
const app = express();
const PORT = process.env.PORT || 10000;
app.use(cors());
app.use(express.json());
app.use(express.urlencoded({ extended: true }));
app.use(express.static(__dirname));
const DATA_DIR = path.join(__dirname, 'data');
const UPLOAD_DIR = path.join(__dirname, 'uploads');
if (!fs.existsSync(DATA_DIR)) fs.mkdirSync(DATA_DIR, { recursive: true });
if (!fs.existsSync(UPLOAD_DIR)) fs.mkdirSync(UPLOAD_DIR, { recursive: true });
const usersFile = path.join(DATA_DIR, 'users.json');
const papersFile = path.join(DATA_DIR, 'papers.json');
function readJSON(f, fb) { try { if (fs.existsSync(f)) return JSON.parse(fs.readFileSync(f, 'utf8')); } catch(e){} return fb; }
function writeJSON(f, d) { fs.writeFileSync(f, JSON.stringify(d, null, 2)); }
const storage = multer.diskStorage({
  destination: function(req, file, cb){ cb(null, UPLOAD_DIR); },
  filename: function(req, file, cb){ cb(null, Date.now() + '-' + file.originalname.replace(/\s+/g,'_')); }
});
const upload = multer({ storage: storage });
app.get('/api/health', function(req,res){ res.json({ ok:true, service:'SCIENOVA' }); });
app.get('/api/papers', function(req,res){ res.json(readJSON(papersFile, [])); });
app.post('/api/papers', upload.single('file'), function(req,res){
  const papers = readJSON(papersFile, []);
  const p = { id: Date.now().toString(), title: req.body.title || 'Untitled', subject: req.body.subject || 'General', type: req.body.type || 'Paper', price: Number(req.body.price||0), adminOnly: req.body.adminOnly==='true', file: req.file ? '/uploads/'+req.file.filename : '', views:0, downloads:0, createdAt: new Date().toISOString() };
  papers.push(p); writeJSON(papersFile, papers); res.json({ ok:true, paper:p });
});
app.post('/api/register', function(req,res){
  const users = readJSON(usersFile, []);
  const { name, phone, password, pin } = req.body || {};
  if(!name || !phone || !password || !pin) return res.status(400).json({ ok:false, error:'All fields required' });
  if(users.find(u=>u.phone===phone)) return res.status(400).json({ ok:false, error:'Phone already registered' });
  const user = { id: Date.now().toString(), name, phone, password, pin, wallet:0, subscribed:false };
  users.push(user); writeJSON(usersFile, users); res.json({ ok:true, user:{ id:user.id, name, phone, wallet:0, subscribed:false } });
});
app.post('/api/login', function(req,res){
  const users = readJSON(usersFile, []);
  const u = users.find(x=>x.phone===(req.body||{}).phone && x.password===(req.body||{}).password);
  if(!u) return res.status(401).json({ ok:false, error:'Wrong phone or password' });
  res.json({ ok:true, user:{ id:u.id, name:u.name, phone:u.phone, wallet:u.wallet||0, subscribed:!!u.subscribed } });
});
app.post('/api/wallet/deposit', function(req,res){
  const users = readJSON(usersFile, []);
  const u = users.find(x=>x.phone===(req.body||{}).phone && x.pin===(req.body||{}).pin);
  if(!u) return res.status(401).json({ ok:false, error:'Wrong PIN for this phone' });
  u.wallet = (u.wallet||0) + Number((req.body||{}).amount||0); writeJSON(usersFile, users);
  res.json({ ok:true, wallet:u.wallet });
});
app.post('/api/buy', function(req,res){
  const users = readJSON(usersFile, []);
  const u = users.find(x=>x.phone===(req.body||{}).phone && x.pin===(req.body||{}).pin);
  if(!u) return res.status(401).json({ ok:false, error:'Wrong PIN' });
  const price = Number((req.body||{}).price||0);
  if((u.wallet||0) < price) return res.status(400).json({ ok:false, error:'Low wallet balance' });
  u.wallet -= price; if(price>=2000) u.subscribed = true; writeJSON(usersFile, users);
  res.json({ ok:true, wallet:u.wallet, subscribed:u.subscribed });
});
app.get('/', function(req,res){ res.sendFile(path.join(__dirname,'index.html')); });
app.use('/uploads', express.static(UPLOAD_DIR));
app.listen(PORT, function(){ console.log('SCIENOVA on ' + PORT); });
