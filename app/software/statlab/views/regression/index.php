<?php
$projects = $projects ?? [];
$presetMode = $presetMode ?? '';
$presetProjectId = $presetProjectId ?? 0;
?>
<div class="container-fluid py-3 py-md-4">
    <div class="statlab-page-header">
        <h3 class="mb-0"><i class="fas fa-chart-line text-success me-2"></i>رگرسیون و همبستگی</h3>
        <a href="<?= stat_url('controller=dashboard') ?>" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-home me-1"></i><span class="d-none d-sm-inline">داشبورد</span>
        </a>
    </div>

    <div class="row g-3">
        <div class="col-12 col-lg-5">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-2 py-md-3"><h6 class="mb-0"><i class="fas fa-sliders-h me-2"></i>تنظیمات تحلیل</h6></div>
                <div class="card-body p-3">
                    <div class="row g-2 mb-3">
                        <div class="col-7">
                            <label class="form-label small mb-1">نوع تحلیل</label>
                            <select id="mode" class="form-select form-select-sm" onchange="renderMode()">
                                <option value="simple" <?= $presetMode==='simple'?'selected':'' ?>>رگرسیون خطی ساده</option>
                                <option value="correlation" <?= $presetMode==='correlation'?'selected':'' ?>>همبستگی پیرسون/اسپیرمن</option>
                                <option value="multiple" <?= $presetMode==='multiple'?'selected':'' ?>>رگرسیون چندگانه</option>
                            </select>
                        </div>
                        <div class="col-5" id="kBox" style="display:none;">
                            <label class="form-label small mb-1">تعداد پیشبین‌ها</label>
                            <input type="number" id="k" class="form-control form-control-sm" min="1" max="5" value="2" onchange="renderMode()">
                        </div>
                    </div>

                    <div class="p-2 mb-3 border rounded" style="background:#f0fdf4;">
                        <label class="form-label fw-bold small mb-1"><i class="fas fa-database me-1 text-success"></i> استفاده از داده‌های پروژه موجود</label>
                        <div class="d-flex gap-2 mb-2">
                            <select id="sourceProject" class="form-select form-select-sm">
                                <option value="0">انتخاب پروژه...</option>
                                <?php foreach ($projects as $p): ?>
                                    <option value="<?= (int)$p['id'] ?>"><?= stat_e($p['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <button type="button" class="btn btn-sm btn-success text-nowrap" onclick="loadProjectDatasets()">
                                <i class="fas fa-download me-1"></i> بارگذاری
                            </button>
                        </div>
                        <div id="datasetPicker" class="small"></div>
                    </div>

                    <div id="inputsBox"></div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small mb-1">سطح α</label>
                            <select id="alpha" class="form-select form-select-sm">
                                <option value="0.05">0.05</option><option value="0.01">0.01</option><option value="0.10">0.10</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label small mb-1">ذخیره در پروژه</label>
                            <select id="targetProject" class="form-select form-select-sm" onchange="onTargetChange()">
                                <option value="0">➕ پروژه جدید</option>
                                <?php foreach ($projects as $p): ?>
                                    <option value="<?= (int)$p['id'] ?>" <?= (int)$p['id']===(int)$presetProjectId?'selected':'' ?>><?= stat_e($p['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3"><input type="text" id="projectName" class="form-control form-control-sm" placeholder="نام پروژه جدید (اختیاری)"></div>

                    <div class="d-grid gap-2">
                        <button class="btn btn-success" onclick="runRegression()"><i class="fas fa-play me-1"></i> اجرای تحلیل</button>
                        <button class="btn btn-outline-info btn-sm" onclick="loadSample()"><i class="fas fa-magic me-1"></i> داده نمونه</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-7">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-success text-white py-2 py-md-3"><h6 class="mb-0"><i class="fas fa-poll me-2"></i>نتایج</h6></div>
                <div class="card-body p-3" id="resultBox">
                    <div class="text-center text-muted py-5">
                        <i class="fas fa-chart-line fa-3x mb-3"></i>
                        <p class="small mb-0">داده‌ها را وارد یا از پروژه بارگذاری کنید، سپس «اجرای تحلیل».</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let loadedDatasets=[]; let sourceIds={}; let lastSimple=null;
function escapeHtml(s){const d=document.createElement('div');d.textContent=String(s??'');return d.innerHTML;}
function showToast(m,t='success'){const e=document.createElement('div');e.className=`alert alert-${t} position-fixed`;e.style.cssText='top:80px;left:50%;transform:translateX(-50%);z-index:10000;min-width:280px;';e.innerHTML=m;document.body.appendChild(e);setTimeout(()=>{e.style.transition='opacity .3s';e.style.opacity='0';setTimeout(()=>e.remove(),300);},2500);}
function ta(k,l){return `<div class="mb-2"><label class="form-label small mb-1">${l}</label><textarea id="in_${k}" class="form-control form-control-sm font-monospace" rows="4" placeholder="دستی یا از پروژه بارگذاری کنید"></textarea></div>`;}

function currentKeys(){
    const mode=document.getElementById('mode').value;
    const k=(mode==='multiple')?Math.min(5,Math.max(1,parseInt(document.getElementById('k').value)||1)):1;
    const keys=[{key:'y',label:'Y (وابسته)'}];
    for(let j=1;j<=k;j++)keys.push({key:'x'+j,label:'X'+j});
    return keys;
}

function renderMode(){
    const mode=document.getElementById('mode').value;
    document.getElementById('kBox').style.display=(mode==='multiple')?'':'none';
    sourceIds={};
    document.getElementById('inputsBox').innerHTML=currentKeys().map(o=>ta(o.key,o.label)).join('');
    document.querySelectorAll('#inputsBox textarea').forEach(t=>{
        t.addEventListener('input',()=>{delete sourceIds[t.id.replace('in_','')];});
    });
    renderDatasetPicker();
}

function onTargetChange(){document.getElementById('projectName').disabled=(parseInt(document.getElementById('targetProject').value)||0)!==0;}

async function loadProjectDatasets(){
    const pid=parseInt(document.getElementById('sourceProject').value)||0;
    if(!pid){showToast('⚠️ ابتدا یک پروژه انتخاب کنید.','warning');return;}
    try{
        const res=await fetch('<?= stat_url('controller=project&action=datasets&id=') ?>'+pid);
        const data=await res.json();
        if(!data.success){showToast('❌ '+data.error,'danger');return;}
        loadedDatasets=data.datasets;
        document.getElementById('targetProject').value=String(pid);
        onTargetChange();
        renderDatasetPicker();
        showToast(`✅ ${loadedDatasets.length} مجموعه داده بارگذاری شد`);
    }catch(e){showToast('❌ خطای شبکه: '+e.message,'danger');}
}

function renderDatasetPicker(){
    const box=document.getElementById('datasetPicker');
    if(!loadedDatasets.length){box.innerHTML='';return;}
    let html='<small class="text-muted d-block mb-1">مقصد هر داده را انتخاب کنید:</small>';
    loadedDatasets.forEach((ds,idx)=>{
        html+=`<div class="d-flex flex-wrap align-items-center gap-1 mb-1 p-1 border rounded bg-white">
            <span class="fw-bold flex-grow-1" style="min-width:100px;">${escapeHtml(ds.name)} <span class="badge bg-secondary">${ds.data.length}</span></span>`;
        currentKeys().forEach(tg=>{html+=`<button type="button" class="btn btn-sm btn-outline-primary py-0" onclick="assignDataset(${idx},'${tg.key}')">→ ${escapeHtml(tg.label)}</button>`;});
        html+='</div>';
    });
    box.innerHTML=html;
}

function assignDataset(idx,key){
    const ds=loadedDatasets[idx];
    const el=document.getElementById('in_'+key);
    if(!el||!ds)return;
    el.value=ds.data.join('\n');
    sourceIds[key]=ds.id;
    showToast(`✅ «${escapeHtml(ds.name)}» در «${escapeHtml(key)}» قرار گرفت`);
}

function loadSample(){
    document.getElementById('mode').value='simple';
    renderMode();
    document.getElementById('in_x1').value=[2,3,4,5,6,7,8,9,10,11].join('\n');
    document.getElementById('in_y').value=[55,60,65,70,74,80,85,88,92,97].join('\n');
    showToast('✅ داده نمونه بارگذاری شد');
}

async function runRegression(){
    const mode=document.getElementById('mode').value;
    const payload={
        mode,
        alpha:parseFloat(document.getElementById('alpha').value),
        k:parseInt(document.getElementById('k').value)||1,
        project_id:parseInt(document.getElementById('targetProject').value)||0,
        project_name:document.getElementById('projectName').value.trim(),
        source_ids:sourceIds,
        y:document.getElementById('in_y').value
    };
    currentKeys().forEach(o=>{if(o.key!=='y')payload[o.key]=document.getElementById('in_'+o.key).value;});

    const box=document.getElementById('resultBox');
    box.innerHTML='<div class="text-center py-4"><div class="spinner-border text-success"></div><p class="small mt-2">در حال محاسبه...</p></div>';
    try{
        const res=await fetch('<?= stat_url('controller=regression&action=analyze') ?>',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify(payload)});
        const data=await res.json();
        if(!data.success){box.innerHTML=`<div class="alert alert-danger py-2 small">❌ ${escapeHtml(data.error)}</div>`;return;}
        renderResult(data);
    }catch(e){box.innerHTML=`<div class="alert alert-danger py-2 small">❌ خطای شبکه: ${escapeHtml(e.message)}</div>`;}
}

function sb(l,v,c=''){return `<div class="col-6 col-md-3"><div class="stat-box"><small>${l}</small><strong class="${c}">${v}</strong></div></div>`;}
function star(p){return p<0.001?'***':(p<0.01?'**':(p<0.05?'*':''));}

function renderResult(data){
    const r=data.result, box=document.getElementById('resultBox');
    let html='';
    if(data.mode==='correlation'){
        html+=`<div class="row g-1 g-md-2 mb-3">`+
            sb('پیرسون r',Number(r.pearson_r).toFixed(4),'text-primary')+
            sb('p پیرسون',Number(r.pearson_p).toFixed(5),r.pearson_p<r.alpha?'text-success':'text-danger')+
            sb('اسپیرمن ρ',Number(r.spearman_rho).toFixed(4),'text-info')+
            sb('p اسپیرمن',Number(r.spearman_p).toFixed(5),r.spearman_p<r.alpha?'text-success':'text-danger')+
            `</div><div class="alert ${r.conclusion==='reject'?'alert-success':'alert-warning'} py-2 small mb-2">شدت رابطه: <strong>${escapeHtml(r.strength)}</strong> | n=${r.n}</div>`;
    }
    if(data.mode==='simple'){
        html+=`<div class="alert alert-info py-2 small mb-3"><strong>مدل:</strong> <span dir="ltr" class="font-monospace">ŷ = ${Number(r.intercept).toFixed(3)} + ${Number(r.slope).toFixed(3)}·x</span></div>
        <div class="row g-1 g-md-2 mb-3">`+
            sb('R²',(Number(r.r2)*100).toFixed(1)+'%','text-primary')+
            sb('شیب b₁',Number(r.slope).toFixed(4)+star(r.p_slope),'text-success')+
            sb('عرض b₀',Number(r.intercept).toFixed(4))+
            sb('Se',Number(r.se).toFixed(4))+
            sb('F',Number(r.F).toFixed(2)+star(r.p_F),'text-warning')+
            sb('p شیب',Number(r.p_slope).toFixed(5),r.p_slope<r.alpha?'text-success':'text-danger')+
            `</div><canvas id="scatterCanvas" style="width:100%;height:auto;max-height:380px;"></canvas>`;
        lastSimple=r;
    }
    if(data.mode==='multiple'){
        html+=`<div class="row g-1 g-md-2 mb-3">`+
            sb('R²',(Number(r.r2)*100).toFixed(1)+'%','text-primary')+
            sb('R² تعدیل',(Number(r.adj_r2)*100).toFixed(1)+'%','text-primary')+
            sb('F',Number(r.F).toFixed(2)+star(r.p_F),'text-warning')+
            sb('p مدل',Number(r.p_F).toFixed(5),r.p_F<r.alpha?'text-success':'text-danger')+
            `</div><div class="table-responsive"><table class="table table-sm table-hover small mb-2">
            <thead class="table-light"><tr><th>ضریب</th><th>برآورد</th><th>SE</th><th>t</th><th>p</th><th>معناداری</th></tr></thead><tbody>`;
        r.coefficients.forEach(c=>{
            html+=`<tr><td class="fw-bold">${escapeHtml(c.name)}</td><td class="font-monospace">${Number(c.beta).toFixed(4)}</td><td class="font-monospace">${Number(c.se).toFixed(4)}</td><td class="font-monospace">${Number(c.t).toFixed(3)}</td><td class="font-monospace">${Number(c.p).toFixed(5)}</td><td>${c.sig?'<span class="badge bg-success">معنادار '+star(c.p)+'</span>':'<span class="badge bg-secondary">نامعنادر</span>'}</td></tr>`;
        });
        html+='</tbody></table></div>';
    }
    html+=`<div class="alert ${r.conclusion==='reject'?'alert-success':'alert-warning'} py-2 mt-2 mb-0"><pre class="mb-0 small" style="white-space:pre-wrap;">${escapeHtml(r.interpretation_fa)}</pre></div>`;
    box.innerHTML=html;
    if(data.mode==='simple')drawScatter(r);
}

function drawScatter(r){
    const c=document.getElementById('scatterCanvas');if(!c)return;
    const dpr=window.devicePixelRatio||1,W=c.clientWidth||700,H=360;
    c.width=W*dpr;c.height=H*dpr;
    const x=c.getContext('2d');x.setTransform(dpr,0,0,dpr,0,0);x.clearRect(0,0,W,H);
    const pts=r.points,xs=pts.map(p=>p.x),ys=pts.map(p=>p.y);
    const mnX=Math.min(...xs),mxX=Math.max(...xs),mnY=Math.min(...ys),mxY=Math.max(...ys);
    const pL=48,pR=14,pT=12,pB=32;
    const sx=v=>pL+(v-mnX)/((mxX-mnX)||1)*(W-pL-pR);
    const sy=v=>H-pB-(v-mnY)/((mxY-mnY)||1)*(H-pT-pB);
    x.strokeStyle='#dee2e6';x.beginPath();x.moveTo(pL,pT);x.lineTo(pL,H-pB);x.lineTo(W-pR,H-pB);x.stroke();
    x.fillStyle='#6c757d';x.font='10px sans-serif';x.textAlign='center';
    for(let i=0;i<=5;i++){const v=mnX+(mxX-mnX)*i/5;x.fillText(Number(v.toFixed(1)),sx(v),H-pB+14);}
    x.textAlign='right';
    for(let i=0;i<=4;i++){const v=mnY+(mxY-mnY)*i/4;x.fillText(Number(v.toFixed(1)),pL-6,sy(v)+3);}
    x.strokeStyle='#198754';x.lineWidth=2;x.beginPath();
    x.moveTo(sx(mnX),sy(r.intercept+r.slope*mnX));x.lineTo(sx(mxX),sy(r.intercept+r.slope*mxX));x.stroke();
    x.fillStyle='#0d6efd';
    pts.forEach(p=>{x.beginPath();x.arc(sx(p.x),sy(p.y),4,0,Math.PI*2);x.fill();});
}
window.addEventListener('resize',()=>{if(lastSimple)drawScatter(lastSimple);});
document.addEventListener('DOMContentLoaded',()=>{renderMode();onTargetChange();});
</script>