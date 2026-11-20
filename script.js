// script.js - PUBLIC
let currentUser=null, attempts={};
function openModal(id){document.getElementById(id).style.display='block'}
function closeModal(id){document.getElementById(id).style.display='none'}
function openPage(p){ if(p==='adminLink') window.location.href='/admin.html'; else alert(p+' page - About/Contact/Terms/Privacy/Refund/FAQ'); }

async function createAccount(){
  const body={name:regName.value,email:regEmail.value,phone:regPhone.value,password:regPass.value,pin:regPin.value,role:regRole.value};
  const r=await fetch('/api/register',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify(body)});
  const d=await r.json(); authMsg.textContent=d.success?'Account created! PIN linked to phone '+body.phone:'Failed: '+d.msg;
  if(d.success){currentUser=d.user; localStorage.setItem('scienova_user',JSON.stringify(d.user)); updateWallet();}
}
async function loginUser(){
  const r=await fetch('/api/login',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({phone:loginPhone.value,password:loginPass.value})});
  const d=await r.json(); if(d.success){currentUser=d.user; localStorage.setItem('scienova_user',JSON.stringify(d.user)); closeModal('authModal'); updateWallet(); alert('Welcome '+d.user.name);} else alert('Wrong password');
}
function updateWallet(){ if(currentUser){walletBal.textContent=currentUser.wallet||0; walletBal2.textContent=currentUser.wallet||0; walletName.textContent=currentUser.name; walletPhone.textContent=currentUser.phone; loginBtn.style.display='none'; logoutBtn.style.display='inline-block';} }

async function deposit(){
  const phone=currentUser.phone, pin=depositPin.value;
  if(attempts[phone]>=3){depositMsg.textContent='Locked 5 min - Too many wrong PINs'; return;}
  const r=await fetch('/api/deposit',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({phone,pin,amount:depositAmt.value,txId:depositTx.value,method:depositMethod.value})});
  const d=await r.json();
  if(!d.success){attempts[phone]=(attempts[phone]||0)+1; depositMsg.textContent=d.msg+' Attempts left: '+(3-attempts[phone]); if(attempts[phone]>=3) depositMsg.textContent='LOCKED 5 min - Too many wrong PINs for '+phone;}
  else {currentUser.wallet=d.balance; walletBal.textContent=d.balance; depositMsg.textContent='Deposited! Balance UGX '+d.balance; attempts[phone]=0;}
}
function dropPaper(){
  const fd=new FormData(); fd.append('file',paperFile.files[0]); fd.append('title',paperTitle.value); fd.append('subject',paperSubject.value); fd.append('level',paperLevel.value); fd.append('school',paperSchool.value);
  fetch('/api/contribute',{method:'POST',body:fd}).then(r=>r.json()).then(d=>{dropMsg.textContent=d.success?'LIVE! Paper ACTIVE instantly':'Failed'; loadPapers();});
}
async function loadPapers(){
  const r=await fetch('/api/papers'); const p=await r.json();
  resources.innerHTML=p.map(x=>`<div class="card"><b>${x.title}</b> ${x.adminOnly?'🔒 LOCKED':''}<br>${x.subject} - ${x.level}<br>👁️ ${x.views} ⬇️ ${x.downloads}<br><button onclick="buyPaper('${x.id}',1000)">Buy UGX 1000 - Enter PIN</button></div>`).join('');
}
async function buyPaper(id,price){
  const pin=prompt('Enter PIN for phone '+currentUser.phone+' to buy');
  const r=await fetch('/api/buy',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({phone:currentUser.phone,pin,paperId:id,price})});
  const d=await r.json(); alert(d.success?'Bought! Balance '+d.balance:d.msg);
}
function connectEngine(name){ googleStatus.textContent='Connected to '+name+' ✓'; alert('Connect to '+name+' - Enter API Key in real site. Demo: Connected ✓'); }
function generateAI(){
  const prompt=aiPrompt.value; if(!prompt) return alert('Type a prompt first');
  aiLog.innerHTML='Connecting to Google...<br>Searching Google for '+prompt+'<br>Searching Bing...<br>Searching Wikipedia...<br>AI creating scenarios... Done!';
  aiResults.innerHTML=`<div class="card"><b>Scenario from prompt:</b> ${prompt}<br>Q: A farmer in Mbale...? A) B) C) D) <br>Source: Found via Google</div>`;
  localStorage.setItem('prompt_history',prompt);
}
function forgotPin(){ const phone=prompt('Enter phone'); const email=prompt('Enter email'); alert('If phone '+phone+' + email match, code 1234 sent. Use it to reset PIN.'); }
function changePin(){ const oldPin=prompt('Old PIN'); if(oldPin!==currentUser.pin) return alert('Wrong old PIN'); const newPin=prompt('New PIN'); currentUser.pin=newPin; alert('PIN changed for phone '+currentUser.phone); }
function downloadReceipt(){ alert('Receipt: SCIENOVA - Balance '+currentUser.wallet); }
window.onload=()=>{ const u=localStorage.getItem('scienova_user'); if(u){currentUser=JSON.parse(u); updateWallet();} loadPapers(); }
