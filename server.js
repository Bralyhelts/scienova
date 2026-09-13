
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

// Ensure directories
const UPLOAD_DIR = path.join(__dirname, 'uploads');
const DATA_DIR = path.join(__dirname, 'data');
if (!fs.existsSync(UPLOAD_DIR)) fs.mkdirSync(UPLOAD_DIR, { recursive: true });
if (!fs.existsSync(DATA_DIR)) fs.mkdirSync(DATA_DIR, { recursive: true });

const USERS_FILE = path.join(DATA_DIR, 'users.json');
const SUBS_FILE = path.join(DATA_DIR, 'subscriptions.json');
const CONTRIB_FILE = path.join(DATA_DIR, 'contributions.json');

function readJson(file, def){ try{ if(fs.existsSync(file)) return JSON.parse(fs.readFileSync(file,'utf8')); }catch(e){} return def; }
function writeJson(file, data){ fs.writeFileSync(file, JSON.stringify(data, null, 2)); }

let users = readJson(USERS_FILE, []); // {id,email,password,name,role,createdAt}
let subscriptions = readJson(SUBS_FILE, []); // {email,plan,transactionId,expiry,createdAt}
let contributions = readJson(CONTRIB_FILE, []);

// Serve static
app.use(express.static(__dirname));
app.use('/uploads', express.static(UPLOAD_DIR));

// --- AUTH ---
app.post('/api/register', (req,res)=>{
  const {name,email,password,role} = req.body;
  if(!email || !password) return res.status(400).json({error:'Email and password required'});
  const exists = users.find(u=>u.email.toLowerCase()===email.toLowerCase());
  if(exists) return res.status(400).json({error:'Account already exists, login instead'});
  const user = {id:Date.now().toString(), name:name||email.split('@')[0], email:email.toLowerCase(), password, role: role||'student', createdAt:new Date().toISOString()};
  users.push(user);
  writeJson(USERS_FILE, users);
  res.json({success:true, user:{id:user.id,name:user.name,email:user.email,role:user.role}});
});

app.post('/api/login', (req,res)=>{
  const {email,password} = req.body;
  const user = users.find(u=>u.email.toLowerCase()===email.toLowerCase() && u.password===password);
  if(!user) return res.status(401).json({error:'Invalid email or password'});
  // check subscription
  const sub = subscriptions.find(s=>s.email===user.email && new Date(s.expiry)>new Date());
  res.json({success:true, user:{id:user.id,name:user.name,email:user.email,role:user.role}, subscription:sub||null});
});

app.post('/api/subscribe', (req,res)=>{
  const {email, transactionId, plan} = req.body;
  if(!email || !transactionId) return res.status(400).json({error:'Email and Transaction ID required'});
  const days = plan==='monthly'?30:7;
  const expiry = new Date(Date.now()+days*24*60*60*1000).toISOString();
  // remove old subs for email
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

// --- RESOURCES LIST ---
app.get('/api/resources', (req,res)=>{
  try{
    const files = fs.readdirSync(UPLOAD_DIR).filter(f=>!f.startsWith('.'));
    const resources = files.map(f=>{
      const stat = fs.statSync(path.join(UPLOAD_DIR,f));
      return {filename:f, url:'/uploads/'+encodeURIComponent(f), size:stat.size, createdAt:stat.mtime};
    });
    res.json(resources);
  }catch(e){ res.json([]); }
});

app.get('/api/contributions', (req,res)=>{
  res.json(contributions);
});

// --- ADMIN: list payments ---
app.get('/api/payments', (req,res)=>{
  res.json(subscriptions.reverse());
});

// --- UPLOAD config ---
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

// Contributor upload (goes to pending folder)
const contribDir = path.join(UPLOAD_DIR,'pending');
if(!fs.existsSync(contribDir)) fs.mkdirSync(contribDir,{recursive:true});
const contribStorage = multer.diskStorage({
  destination:(req,file,cb)=>cb(null,contribDir),
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

// Approve contribution
app.post('/api/approve-contribution', (req,res)=>{
  const {id} = req.body;
  const item = contributions.find(c=>c.id===id);
  if(!item) return res.status(404).json({error:'Not found'});
  const oldPath = path.join(contribDir, item.filename);
  const newPath = path.join(UPLOAD_DIR, item.filename);
  if(fs.existsSync(oldPath)) fs.renameSync(oldPath,newPath);
  item.status='approved';
  writeJson(CONTRIB_FILE, contributions);
  res.json({success:true});
});

app.get('*', (req,res)=>{
  res.sendFile(path.join(__dirname,'index.html'));
});

app.listen(PORT, ()=>console.log('SCIENOVA running on port', PORT));
