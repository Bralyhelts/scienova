/* SCIENOVA - ALL BUTTONS + PAYWALL - LIVE SERVER VERSION */

const resources={
 Biology:["Cell Biology Notes","Genetics Revision Guide","A-Level Biology Past Paper Pack"],
 Chemistry:["Organic Chemistry Summary","Mole Concept Worked Examples","Practical Chemistry Guide"],
 Mathematics:["Pure Mathematics Formula Sheet","Calculus Worked Examples","Statistics Revision Pack"],
 Physics:["Mechanics Notes","Electricity & Circuits Guide","Waves and Optics Revision"]
};

function isSubscribed(){
  return localStorage.getItem('scienova_subscribed') === 'true';
}
function isLogged(){
  return localStorage.getItem('scienova_logged') === 'true';
}

function openResource(subject){
  if(!isLogged()){
    alert("🔒 Please Log in first. Scroll down to Member Access.");
    document.querySelector('#login')?.scrollIntoView({behavior:"smooth"});
    return;
  }
  if(!isSubscribed()){
    alert(`🔒 Subscription required!\n\nTo access ${subject} resources, you must pay:\n\nStandard: UGX 2,000\nContributor: UGX 1,000\n\nMTN MoMo: 076659663 (TUSHEMERIRWE CAROLYNE)\n\nThen enter Transaction ID in subscription section.`);
    document.querySelector('#subscribe')?.scrollIntoView({behavior:"smooth"});
    return;
  }

 const p=document.getElementById("resourcePanel");
 if(!p) return;
 p.classList.remove("hidden");
 let uploads = [];
 try{ uploads = JSON.parse(localStorage.getItem('scienova_uploads') || '[]'); }catch{}
 let subjectUploads = uploads.filter(u => u.subject === subject);
 let base = resources[subject] || [];
 
 let html = `<h3>${subject} resources ✅ Unlocked</h3>`;
 html += base.map((x,i)=>`<a href="#" onclick="alert('📚 Opening: ${x}');return false;" style="display:block;margin:6px 0;padding:8px 12px;background:rgba(255,255,255,0.15);border-radius:8px;">${i+1}. ${x} ↗</a>`).join("");
 
 if(subjectUploads.length > 0){
   html += `<hr style="margin:15px 0;border:0;border-top:1px solid #ffffff33"><h4>📁 Admin Uploads:</h4>`;
   html += subjectUploads.map(u=>`<a href="${u.fileData}" download="${u.fileName}" style="display:block;margin:8px 0;background:#ffffff22;padding:10px 12px;border-radius:8px">📄 ${u.title} - ${u.fileName} ⬇ Download</a>`).join("");
 }
 html += `<br><button onclick="document.getElementById('resourcePanel').classList.add('hidden')" style="padding:8px 14px;border-radius:8px;border:0;cursor:pointer;">Close</button>`;
 p.innerHTML = html;
 p.scrollIntoView({behavior:"smooth",block:"center"});
}

function filterCards(){
 const q=(document.getElementById("search")?.value || "").toLowerCase();
 document.querySelectorAll(".subject").forEach(c=>{
   const name = (c.dataset.name || "").toLowerCase();
   const text = (c.innerText || "").toLowerCase();
   c.style.display = (name.includes(q) || text.includes(q)) ? "" : "none";
 });
}

function showReviewForm(){
  document.getElementById('reviewForm')?.classList.remove('hidden');
  document.getElementById('reviewForm')?.scrollIntoView({behavior:"smooth"});
}
function submitReview(e){
  e.preventDefault();
  const name = document.getElementById('reviewName').value.trim();
  const text = document.getElementById('reviewText').value.trim();
  if(!name || !text) return;
  
  if(!isLogged()){
    alert("Please log in first to add review");
    document.querySelector('#login')?.scrollIntoView({behavior:"smooth"});
    return;
  }

  let reviews = [];
  try{ reviews = JSON.parse(localStorage.getItem('scienova_reviews') || '[]'); }catch{}
  reviews.unshift({ name, text, date: new Date().toLocaleDateString() });
  localStorage.setItem('scienova_reviews', JSON.stringify(reviews));

  const r=document.createElement("article");r.className="review";
  r.innerHTML=`<div class="avatar">${name.slice(0,2).toUpperCase()}</div><div><b>${name}</b><span>New review • ${new Date().toLocaleDateString()}</span><p>“${text.replaceAll("<","&lt;").replaceAll(">","&gt;")}”</p></div>`;
  document.getElementById("reviews")?.prepend(r);
  
  document.getElementById('reviewMsg').textContent = "✅ Review posted!";
  e.target.reset();
  setTimeout(()=>{ document.getElementById('reviewForm').classList.add('hidden'); document.getElementById('reviewMsg').textContent=''; },1500);
}

function addReview(){ showReviewForm(); }

function login(e){
 e.preventDefault();
 const email = document.getElementById('email')?.value || 'student';
 localStorage.setItem('scienova_user', email);
 localStorage.setItem('scienova_logged', 'true');
 const msg = document.getElementById('loginMsg');
 if(msg){
   msg.style.color = "green";
   msg.textContent = "✅ Logged in as " + email + ". Now subscribe to unlock library.";
 }
 const subEmail = document.getElementById('subEmail');
 if(subEmail && !subEmail.value) subEmail.value = email;
 
 if(!isSubscribed()){
   setTimeout(()=>document.querySelector('#subscribe')?.scrollIntoView({behavior:"smooth"}),800);
 }
}

function subscribe(e){
  e.preventDefault();
  const email = document.getElementById('subEmail').value.trim();
  const txn = document.getElementById('txnId').value.trim();
  const plan = document.getElementById('plan').value;
  if(!email || !txn){
    alert("Enter email and Transaction ID");
    return;
  }
  localStorage.setItem('scienova_subscribed','true');
  localStorage.setItem('scienova_sub_email', email);
  localStorage.setItem('scienova_txn', txn);
  localStorage.setItem('scienova_plan', plan);
  localStorage.setItem('scienova_sub_date', new Date().toISOString());
  
  const status = document.getElementById('subStatus');
  if(status){
    status.style.color = "green";
    status.textContent = `✅ Access unlocked! Plan UGX ${plan} - Transaction ${txn}. You can now view all resources.`;
  }
  alert(`✅ Thank you! Subscription activated.\n\nEmail: ${email}\nPlan: UGX ${plan}\nTxn: ${txn}\n\nYou can now access all library resources.`);
  document.querySelector('#library')?.scrollIntoView({behavior:"smooth"});
}

function toggleMenu(){
 const nav=document.querySelector("header nav");
 if(!nav) return;
 const isHidden = nav.style.display === "none" || getComputedStyle(nav).display === "none";
 if(isHidden || nav.style.display === ""){
   nav.style.display="flex";nav.style.position="absolute";nav.style.top="76px";nav.style.right="0";nav.style.left="0";
   nav.style.padding="20px";nav.style.background="#fff";nav.style.flexDirection="column";nav.style.zIndex="99";
   nav.style.boxShadow="0 10px 30px rgba(0,0,0,0.1)";
 } else { nav.style.display="none"; }
}

window.addEventListener('DOMContentLoaded', ()=>{
  const savedUser = localStorage.getItem('scienova_user');
  const loginMsg = document.getElementById('loginMsg');
  if(savedUser && loginMsg && localStorage.getItem('scienova_logged')==='true'){
    loginMsg.textContent = "✅ Already logged in as " + savedUser;
  }
  const subEmail = document.getElementById('subEmail');
  if(savedUser && subEmail) subEmail.value = savedUser;

  const subStatus = document.getElementById('subStatus');
  if(isSubscribed() && subStatus){
    const plan = localStorage.getItem('scienova_plan') || '2000';
    subStatus.style.color="green";
    subStatus.textContent = `✅ Active subscription: UGX ${plan} - Access unlocked`;
  }

  let reviews = [];
  try{ reviews = JSON.parse(localStorage.getItem('scienova_reviews') || '[]'); }catch{}
  reviews.reverse().forEach(rev=>{
    const r=document.createElement("article");r.className="review";
    r.innerHTML=`<div class="avatar">${rev.name.slice(0,2).toUpperCase()}</div><div><b>${rev.name}</b><span>Saved • ${rev.date||''}</span><p>“${(rev.text||'').replaceAll("<","&lt;").replaceAll(">","&gt;")}”</p></div>`;
    document.getElementById("reviews")?.prepend(r);
  });
});
