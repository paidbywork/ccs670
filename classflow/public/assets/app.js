(() => {
 const button=document.getElementById('menuToggle'), sidebar=document.getElementById('appSidebar'), overlay=document.getElementById('menuOverlay');
 const close=()=>{document.body.classList.remove('nav-open'); button?.setAttribute('aria-expanded','false');};
 button?.addEventListener('click',()=>{const isOpen=document.body.classList.toggle('nav-open');button.setAttribute('aria-expanded',String(isOpen));});
 overlay?.addEventListener('click',close);
 sidebar?.querySelectorAll('a').forEach(link=>link.addEventListener('click',close));
 document.addEventListener('keydown',event=>{if(event.key==='Escape')close();});
 const toggle=document.getElementById('togglePassword'), password=document.getElementById('password');
 toggle?.addEventListener('click',()=>{const visible=password.type==='text';password.type=visible?'password':'text';toggle.textContent=visible?'Show':'Hide';toggle.setAttribute('aria-label',visible?'Show password':'Hide password');toggle.setAttribute('aria-pressed',String(!visible));});
})();
