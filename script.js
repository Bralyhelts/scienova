
let currentUser = JSON.parse(localStorage.getItem('scienova_user')||'null');
let currentSub = JSON.parse(localStorage.getItem('scienova_sub')||'null');
let selectedPlan = 'weekly';

function updateNav(){
  const loginBtn = document.getElementById('loginBtn');
  const logoutBtn = document.getElementById('logoutBtn');
  const userInfo = document.getElementById('userInfo');
  const navContrib = document.getElementById('navContributor');
  const navAdmin = document.getElementById('navAdmin');
  const subStatus = document.getElementById('subStatus');
  if(currentUser){
    loginBtn.style.display='none';
    logoutBtn.style.display='inline-block';
    userInfo.textContent = currentUser.name+' ('+currentUser.role+')';
    if(currentUser.role==='contributor' || currentUser.role==='admin'){
      navContrib.style.display='inline-block';
    }
    if(currentUser.email==='bralys@example.com' || currentUser.role==='admin'){
      navAdmin.style.display='inline-block';
    }
  }else{
    loginBtn.style.display='inline-block';
    logoutBtn.style.display='none';
    userInfo.textContent='';
    navContrib.style.display='none';
    navAdmin.style.display='none';
  }
  // check sub
  if(currentUser){
    fetch('/api/check-subscription?email='+encodeURIComponent(currentUser.email))
      .then(r=>r.json()).then(d=>{
        if(d.subscribed){
          currentSub = d.subscription;
          localStorage.setItem('scienova_sub', JSON.stringify(currentSub));
          subStatus.textContent = '✅ Subscribed ('+currentSub.plan+') till '+new Date(currentSub.expiry).toLocaleDateString();
          subStatus.className='subStatus active';
        }else{
          currentSub=null;
          localStorage.removeItem('scienova_sub');
          subStatus.textContent='❌ Not subscribed - Subscribe to download';
          subStatus.className='subStatus';
        }
      });
  }else{
    subStatus.textContent='🔒 Login required to download';
  }
}

// Auth modal logic
const authModal = document.getElementById('authModal');
document.getElementById('loginBtn').onclick = ()=>{ authModal.style.display='block'; };
document.getElementById('closeAuth').onclick = ()=>authModal.style.display='none';
let authMode='register';
document.getElementById('tabLogin').onclick = ()=>{ authMode='login'; document.getElementById('authTitle').textContent='Login'; document.getElementById('tabLogin').classList.add('active'); document.getElementById('tabRegister').classList.remove('active'); document.getElementById('authName').style.display='none'; document.getElementById('authRole').style.display='none'; document.getElementById('authSubmit').textContent='Login'; };
document.getElementById('tabRegister').onclick = ()=>{ authMode='register'; document.getElementById('authTitle').textContent='Create Account'; document.getElementById('tabRegister').classList.add('active'); document.getElementById('tabLogin').classList.remove('active'); document.getElementById('authName').style.display='block'; document.getElementById('authRole').style.display='block'; document.getElementById('authSubmit').textContent='Create Account'; };
document.getElementById('tabRegister').click();

document.getElementById('authSubmit').onclick = async ()=>{
  const name=document.getElementById('authName').value;
  const email=document.getElementById('authEmail').value.trim();
  const password=document.getElementById('authPass').value;
  const role=document.getElementById('authRole').value;
  const msg=document.getElementById('authMsg');
  if(!email||!password){ msg.textContent='Fill email and password'; return; }
  try{
    const url = authMode==='register'?'/api/register':'/api/login';
    const body = authMode==='register'?{name,email,password,role}:{email,password};
    const res = await fetch(url,{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify(body)});
    const data = await res.json();
    if(!res.ok) throw new Error(data.error);
    currentUser=data.user;
    localStorage.setItem('scienova_user', JSON.stringify(currentUser));
    if(data.subscription){ currentSub=data.subscription; localStorage.setItem('scienova_sub', JSON.stringify(data.subscription)); }
    msg.textContent='Success!';
    authModal.style.display='none';
    updateNav();
    loadResources();
  }catch(e){ msg.textContent=e.message; }
};

document.getElementById('logoutBtn').onclick = ()=>{
  currentUser=null; currentSub=null;
  localStorage.removeItem('scienova_user'); localStorage.removeItem('scienova_sub');
  updateNav(); loadResources();
};

document.getElementById('navAdmin').onclick = ()=>{ window.location.href='/admin.html'; };

// Contributor
const contribModal = document.getElementById('contribModal');
document.getElementById('navContributor').onclick = ()=>{ contribModal.style.display='block'; };
document.getElementById('closeContrib').onclick = ()=>contribModal.style.display='none';
document.getElementById('contribSubmit').onclick = async ()=>{
  if(!currentUser){ alert('Login first as contributor'); return; }
  const file = document.getElementById('contribFile').files[0];
  const subject = document.getElementById('contribSubject').value;
  const desc = document.getElementById('contribDesc').value;
  const msg = document.getElementById('contribMsg');
  if(!file){ msg.textContent='Select file'; return; }
  const fd = new FormData();
  fd.append('file', file);
  fd.append('email', currentUser.email);
  fd.append('subject', subject);
  fd.append('description', desc);
  msg.textContent='Uploading...';
  const res = await fetch('/api/contributor-upload',{method:'POST',body:fd});
  const data = await res.json();
  msg.textContent = data.message || 'Uploaded!';
};

function canDownload(){
  if(!currentUser){ return {ok:false, reason:'Please create account and login first'}; }
  if(!currentSub || new Date(currentSub.expiry)<=new Date()){ return {ok:false, reason:'Subscription required - Pay UGX 2000 to 076659663'}; }
  return {ok:true};
}

// Resources
async function loadResources(filter='all'){
  const res = await fetch('/api/resources');
  const files = await res.json();
  const container = document.getElementById('resources');
  container.innerHTML='';
  files.filter(f=>{
    if(filter==='all') return true;
    return f.filename.toLowerCase().includes(filter.toLowerCase());
  }).forEach(f=>{
    const div=document.createElement('div');
    div.className='card';
    div.innerHTML=`<h3>${f.filename}</h3><p>${(f.size/1024).toFixed(1)} KB</p><button class="dlBtn">Download</button>`;
    div.querySelector('.dlBtn').onclick = ()=>{
      const check = canDownload();
      if(!check.ok){
        if(!currentUser){ authModal.style.display='block'; }
        else{ payModal.style.display='block'; document.getElementById('payMsg').textContent=check.reason; }
        return;
      }
      // authorized download
      window.location.href = f.url;
    };
    container.appendChild(div);
  });
}

// Filters
document.querySelectorAll('.filterBtn').forEach(b=>{
  b.onclick = ()=>{
    document.querySelectorAll('.filterBtn').forEach(x=>x.classList.remove('active'));
    b.classList.add('active');
    loadResources(b.dataset.sub);
  };
});

// Paywall
const payModal = document.getElementById('payModal');
document.getElementById('closePay').onclick=()=>payModal.style.display='none';
document.querySelectorAll('.plan').forEach(p=>{
  p.onclick=()=>{
    document.querySelectorAll('.plan').forEach(x=>x.classList.remove('selected'));
    p.classList.add('selected');
    selectedPlan=p.dataset.plan;
  };
});
document.querySelector('.plan[data-plan="weekly"]').classList.add('selected');
document.getElementById('confirmPay').onclick = async ()=>{
  const tid=document.getElementById('transId').value.trim();
  const msg=document.getElementById('payMsg');
  if(!tid){ msg.textContent='Enter Transaction ID'; return; }
  if(!currentUser){ msg.textContent='Login first'; return; }
  const res = await fetch('/api/subscribe',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({email:currentUser.email, transactionId:tid, plan:selectedPlan})});
  const data=await res.json();
  if(data.success){
    currentSub=data.subscription;
    localStorage.setItem('scienova_sub', JSON.stringify(currentSub));
    msg.textContent='✅ Subscribed! You can now download';
    setTimeout(()=>{payModal.style.display='none'; updateNav();},1000);
  }else{ msg.textContent=data.error; }
};

window.onclick = (e)=>{ if(e.target===authModal) authModal.style.display='none'; if(e.target===payModal) payModal.style.display='none'; if(e.target===contribModal) contribModal.style.display='none'; };

updateNav();
loadResources();
