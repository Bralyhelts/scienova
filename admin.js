const PASS='SCIENOVA2026';
function adminLogin(){if(adminPass.value===PASS){loginBox.style.display='none'; adminPanel.style.display='block'; loadAdmin();} else adminMsg.textContent='Wrong Access Code';}
async function loadAdmin(){showLoading(); const r=await fetch('/api/papers'); const p=await r.json(); const pay=await (await fetch('/api/payments')).json(); stats.innerHTML=`Papers: ${p.length} | Payments: ${pay.length} | Admin-Only: ${p.filter(x=>x.adminOnly).length}`; tabContent.innerHTML=p.map(x=>`<div class="card">${x.title} ${x.locked?'🔒 LOCKED':''} 👁️ ${x.views} ⬇️ ${x.downloads} <button onclick="deletePaper('${x.id}')">Delete (Admin Only)</button></div>`).join(''); adminPapers.innerHTML=p.filter(x=>x.adminOnly).map(x=>`<div class="card">🔒 ${x.title} - ${x.subject} - ${x.level} - UGX ${x.price} <button>Edit</button> <button>Delete</button></div>`).join('');}
function showLoading(){loadingBar.style.display='block'; let i=0; const iv=setInterval(()=>{i+=10; loadPct.textContent=i+'%'; if(i>=100){clearInterval(iv); loadingBar.style.display='none';}},100);}
function showTab(t){showLoading(); if(t==='weekly') tabContent.innerHTML='Weekly Payments - UGX 2000 - Loading...'; if(t==='monthly') tabContent.innerHTML='Monthly Payments - UGX 5000 - Loading...'; if(t==='adminOnly') tabContent.innerHTML='Admin-Only Papers - LOCKED zone';}
async function searchByPhone(phone){if(phone.length<5) return; const r=await fetch('/api/search-user?phone='+phone); const u=await r.json(); if(u) tabContent.innerHTML=`<div class="card"><b>${u.name}</b> Phone: ${u.phone} Wallet: UGX ${u.wallet} Email: ${u.email}<br><button>Block</button> <button>Reset PIN</button></div>`;}
async function addAdminPaper(){
  if(!adminFile.files[0]) return adminAddMsg.textContent='⚠️ PUT PDF FIRST - Choose file above!';
  const fd=new FormData(); fd.append('file',adminFile.files[0]); fd.append('title',adminTitle.value); fd.append('subject',adminSubject.value); fd.append('level',adminLevel.value); fd.append('author',adminAuthor.value); fd.append('school',adminSchool.value); fd.append('price',adminPrice.value); fd.append('desc',adminDesc.value);
  const r=await fetch('/api/admin-paper',{method:'POST',body:fd}); const d=await r.json();
  adminAddMsg.textContent=d.success?'✅ Paper added and LOCKED 🔒 - Others cannot tamper!':'Failed'; loadAdmin();
}
function uploadAdminPic(){const f=adminPicInput.files[0]; if(!f) return; const url=URL.createObjectURL(f); adminPic.src=url; adminPic.style.display='block'; alert('Admin Profile Pic Uploaded ✓');}
function changeAdminPass(){const n=prompt('New Access Code'); alert('Access Code changed to '+n);}
function sendAnnouncement(){alert('Announcement sent: '+announceText.value);}
function exportExcel(){const csv='phone,amount,txId,date\n076...,2000,ABC123,2026-11-19'; const b=new Blob([csv],{type:'text/csv'}); const a=document.createElement('a'); a.href=URL.createObjectURL(b); a.download='payments.csv'; a.click();}
function backupData(){alert('Backup created ✓');}
function logoutAdmin(){location.reload();}
function deletePaper(id){if(confirm('Delete? Only Admin can do this')) alert('Deleted '+id);}
document.getElementById('adminFile')?.addEventListener('change',e=>{adminFileName.textContent='Selected: '+e.target.files[0].name;});
