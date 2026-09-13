
const express = require('express');
const multer = require('multer');
const path = require('path');
const fs = require('fs');
const cors = require('cors');

const app = express();
const PORT = process.env.PORT || 3000;

app.use(cors());
app.use(express.json());
app.use(express.urlencoded({ extended: true }));

// CRASH FIX: Ensure directories exist (Railway compatible)
const UPLOAD_DIR = path.join(__dirname, 'uploads');
const DATA_DIR = path.join(__dirname, 'data');
const PENDING_DIR = path.join(UPLOAD_DIR, 'pending');

[UPLOAD_DIR, DATA_DIR, PENDING_DIR].forEach(dir=>{
  if(!fs.existsSync(dir)) fs.mkdirSync(dir, {recursive:true});
});

const USERS_FILE = path.join(DATA_DIR, 'users.json');
const SUBS_FILE = path.join(DATA_DIR, 'subscriptions.json');
const CONTRIB_FILE = path.join(DATA_DIR, 'contributions.json');
const STATS_FILE = path.join(DATA_DIR, 'stats.json');

function readJson(file, def){ try{ if(fs.existsSync(file)) return JSON.parse(fs.readFileSync(file,'utf8')); }catch(e){ console.log('read error', file, e.message);} return def; }
function writeJson(file, data){ try{ fs.writeFileSync(file, JSON.stringify(data, null, 2)); }catch(e){ console.log('write error', e.message);} }

let users = readJson(USERS_FILE, []);
let subscriptions = readJson(SUBS_FILE, []);
let contributions = readJson(CONTRIB_FILE, []);
let stats = readJson(STATS_FILE, {}); // {filename: {views, downloads}}

if(!fs.existsSync(USERS_FILE)) writeJson(USERS_FILE, []);
if(!fs.existsSync(SUBS_FILE)) writeJson(SUBS_FILE, []);
if(!fs.existsSync(CONTRIB_FILE)) writeJson(CONTRIB_FILE, []);
if(!fs.existsSync(STATS_FILE)) writeJson(STATS_FILE, {});

app.use(express.static(__dirname));
app.use('/uploads', express.static(UPLOAD_DIR));

// --- AUTH ---
app.post('/api/register', (req,res)=>{
  const {name,email,password,role} = req.body;
  if(!email || !password) return res.status(400).json({error:'Email and password required'});
  if(users.find(u=>u.email.toLowerCase()===email.toLowerCase())) return res.status(400).json({error:'Account exists, login instead'});
  const user = {id:Date.now().toString(), name:name||email.split('@')[0], email:email.toLowerCase(), password, role: role||'student', createdAt:new Date().toISOString()};
  users.push(user);
  writeJson(USERS_FILE, users);
  res.json({success:true, user:{id:user.id,name:user.name,email:user.email,role:user.role}});
});

app.post('/api/login', (req,res)=>{
  const {email,password} = req.body;
  const user = users.find(u=>u.email.toLowerCase()===email.toLowerCase() && u.password===password);
  if(!user) return res.status(401).json({error:'Invalid email or password'});
  const sub = subscriptions.find(s=>s.email===user.email && new Date(s.expiry)>new Date());
  res.json({success:true, user:{id:user.id,name:user.name,email:user.email,role:user.role}, subscription:sub||null});
});

app.post('/api/subscribe', (req,res)=>{
  const {email, transactionId, plan} = req.body;
  if(!email || !transactionId) return res.status(400).json({error:'Email and Transaction ID required'});
  const days = plan==='monthly'?30:7;
  const expiry = new Date(Date.now()+days*24*60*60*1000).toISOString();
  subscriptions = subscriptions.filter(s=>s.email!==email.toLowerCase());
  const sub = {email:email.toLowerCase(), plan:plan||'weekly', transactionId, expiry, createdAt:new Date().toISOString()};
  subscriptions.push(sub);
  writeJson(SUBS_FILE, subscriptions);
  res.json({success:true, subscription:sub});
});

app.get('/api/check-subscription', (req,res)=>{
  const email = (req.query.email||'').toLowerCase();
  const sub = subscriptions.find(s=>s.email===email && new Date(s.expiry)>new Date());
  res.json({subscribed:!!sub, subscription:sub||null});
});

// --- RESOURCES WITH VIEWS/DOWNLOADS ---
app.get('/api/resources', (req,res)=>{
  try{
    const files = fs.readdirSync(UPLOAD_DIR).filter(f=>!f.startsWith('.') && f!=='pending' && fs.statSync(path.join(UPLOAD_DIR,f)).isFile());
    const resources = files.map(f=>{
      const stat = fs.statSync(path.join(UPLOAD_DIR,f));
      const s = stats[f] || {views:0, downloads:0};
      return {filename:f, url:'/uploads/'+encodeURIComponent(f), size:stat.size, createdAt:stat.mtime, views:s.views, downloads:s.downloads};
    }).sort((a,b)=>new Date(b.createdAt)-new Date(a.createdAt));
    res.json(resources);
  }catch(e){ console.log(e); res.json([]); }
});

// Track view
app.post('/api/view/:filename', (req,res)=>{
  const fn = req.params.filename;
  if(!stats[fn]) stats[fn] = {views:0, downloads:0};
  stats[fn].views++;
  writeJson(STATS_FILE, stats);
  res.json({success:true, stats:stats[fn]});
});

// Track download
app.post('/api/download/:filename', (req,res)=>{
  const fn = req.params.filename;
  if(!stats[fn]) stats[fn] = {views:0, downloads:0};
  stats[fn].downloads++;
  writeJson(STATS_FILE, stats);
  res.json({success:true, stats:stats[fn]});
});

app.get('/api/contributions', (req,res)=>{ res.json(contributions); });
app.get('/api/payments', (req,res)=>{ res.json([...subscriptions].reverse()); });
app.get('/api/stats', (req,res)=>{ res.json(stats); });

// --- UPLOAD ---
const storage = multer.diskStorage({
  destination:(req,file,cb)=>cb(null,UPLOAD_DIR),
  filename:(req,file,cb)=>{
    const safe = file.originalname.replace(/[^a-zA-Z0-9._-]/g,'_');
    cb(null, Date.now()+'_'+safe);
  }
});
const upload = multer({storage});
app.post('/api/upload', upload.single('file'), (req,res)=>{
  if(!req.file) return res.status(400).json({error:'No file'});
  res.json({success:true, filename:req.file.filename, url:'/uploads/'+req.file.filename});
});

const contribStorage = multer.diskStorage({
  destination:(req,file,cb)=>cb(null,PENDING_DIR),
  filename:(req,file,cb)=>cb(null, Date.now()+'_'+file.originalname.replace(/[^a-zA-Z0-9._-]/g,'_'))
});
const contribUpload = multer({storage:contribStorage});

app.post('/api/contributor-upload', contribUpload.single('file'), (req,res)=>{
  const {email,subject,description} = req.body;
  const entry = {id:Date.now().toString(), email, subject, description, filename:req.file.filename, originalName:req.file.originalname, status:'pending', createdAt:new Date().toISOString()};
  contributions.push(entry);
  writeJson(CONTRIB_FILE, contributions);
  res.json({success:true, message:'Uploaded! Admin will review.'});
});

app.post('/api/approve-contribution', (req,res)=>{
  const {id} = req.body;
  const item = contributions.find(c=>c.id===id);
  if(!item) return res.status(404).json({error:'Not found'});
  const oldPath = path.join(PENDING_DIR, item.filename);
  const newPath = path.join(UPLOAD_DIR, item.filename);
  if(fs.existsSync(oldPath)) fs.renameSync(oldPath,newPath);
  item.status='approved';
  writeJson(CONTRIB_FILE, contributions);
  res.json({success:true});
});

app.post('/api/reject-contribution', (req,res)=>{
  const {id} = req.body;
  contributions = contributions.filter(c=>c.id!==id);
  writeJson(CONTRIB_FILE, contributions);
  res.json({success:true});
});

app.get('*', (req,res)=>{ res.sendFile(path.join(__dirname,'index.html')); });

// CRITICAL FIX FOR RAILWAY: bind 0.0.0.0
app.listen(PORT, '0.0.0.0', ()=>console.log('SCIENOVA running on port', PORT, 'host 0.0.0.0'));
