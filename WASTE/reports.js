// Reports page script — extracted from index.js
(function(){
  // safety: only run on the reports page
  if(!document.getElementById || !document.getElementById('reports-status')) return;

  // helper to write status/errors to the page for diagnostics
  function _safeGet(id){ try { return document.getElementById(id); } catch(e){ return null; } }
  const _statusEl = _safeGet('reports-status');
  function setStatus(msg, isError){
    if(_statusEl){
      _statusEl.textContent = msg;
      if(isError){ _statusEl.style.background='#fdecea'; _statusEl.style.border='1px solid #f5c6cb'; _statusEl.style.color='#721c24'; }
      else { _statusEl.style.background='#e9f7ef'; _statusEl.style.border='1px solid #c3e6cb'; _statusEl.style.color='#0b3d08'; }
    }
    console.log('reports-status:', msg);
  }

  // register global handlers so errors show up visibly (only on reports page)
  window.onerror = function(msg, url, line, col, err){ setStatus('Error: '+msg, true); console.error(err || msg); };
  window.addEventListener('unhandledrejection', function(ev){ setStatus('Unhandled rejection: '+(ev.reason && ev.reason.message?ev.reason.message:ev.reason), true); console.error(ev.reason); });

  setStatus('Loading reports...');

  // simple demo data generator
  const districts = ['District I','District II','District III','District IV'];
  const barangays = Array.from({length:8}, (_,i)=>'Barangay '+(i+1));
  const types = ['General','Recyclable','Organic','Hazardous'];
  const demo = [];
  const start = new Date(); start.setMonth(start.getMonth()-6);
  function rand(min,max){return Math.floor(Math.random()*(max-min+1))+min}
  for(let d=0; d<120; d++){
    const date = new Date(start); date.setDate(date.getDate()+d);
    demo.push({
      date: date.toISOString().slice(0,10),
      district: districts[rand(0,districts.length-1)],
      barangay: barangays[rand(0,barangays.length-1)],
      type: types[rand(0,types.length-1)],
      volume: rand(5,200),
      compliance: rand(60,100)
    });
  }

  // helpers
  function getFiltered(){
    const from = document.getElementById('filter-from').value;
    const to = document.getElementById('filter-to').value;
    const district = document.getElementById('filter-district').value;
    return demo.filter(r=>{
      if(from && r.date < from) return false;
      if(to && r.date > to) return false;
      if(district && r.district !== district) return false;
      return true;
    });
  }

  // Chart.js guards
  const hasChart = typeof Chart !== 'undefined';
  const pieEl = document.getElementById('pieChart');
  const barEl = document.getElementById('barChart');
  let pieChart=null, barChart=null;

  function renderCharts(){
    const data = getFiltered();
    // pie: waste composition by type
    const comp = {};
    data.forEach(r=> comp[r.type]=(comp[r.type]||0)+r.volume);
    const pieLabels = Object.keys(comp);
    const pieValues = pieLabels.map(l=>comp[l]);
    if(hasChart){ if(pieChart) pieChart.destroy(); pieChart = new Chart(pieEl.getContext('2d'), {type:'pie', data:{labels:pieLabels,datasets:[{data:pieValues, backgroundColor:['#84994F','#FFE797','#FCB53B','#A72703']}]}}); }
    else{ // basic fallback: text
      const ctx = pieEl.getContext('2d'); ctx.clearRect(0,0,pieEl.width,pieEl.height); ctx.fillStyle='#333'; ctx.fillText('Pie: '+pieLabels.map((l,i)=>l+':'+pieValues[i]).join(' | '),10,20);
    }

    // bar: average compliance per barangay
    const byBarangay = {};
    data.forEach(r=>{ byBarangay[r.barangay]=byBarangay[r.barangay]||[]; byBarangay[r.barangay].push(r.compliance); });
    const barLabels = Object.keys(byBarangay);
    const barValues = barLabels.map(b=>Math.round(byBarangay[b].reduce((a,c)=>a+c,0)/byBarangay[b].length));
    if(hasChart){ if(barChart) barChart.destroy(); barChart = new Chart(barEl.getContext('2d'), {type:'bar', data:{labels:barLabels,datasets:[{label:'Compliance %', data:barValues, backgroundColor:'#84994F'}]}, options:{plugins:{legend:{display:false}}}}); }
    else{ const ctx = barEl.getContext('2d'); ctx.clearRect(0,0,barEl.width,barEl.height); ctx.fillStyle='#333'; ctx.fillText('Bar: '+barLabels.map((l,i)=>l+':'+barValues[i]).join(' | '),10,20); }

    renderHeatmap(data);
  }

  function renderHeatmap(data){
    const months = [...new Set(data.map(r=>r.date.slice(0,7)))].sort();
    const container = document.getElementById('heatmap'); container.innerHTML='';
    if(months.length===0){ container.textContent='No data'; return; }
    const grid = document.createElement('div'); grid.style.display='grid'; grid.style.gridTemplateColumns = '140px repeat('+months.length+', 1fr)'; grid.style.gap='6px';
    grid.appendChild(Object.assign(document.createElement('div'),{textContent:'Barangay / Month', style:'font-weight:700;padding:6px'}));
    months.forEach(m=> grid.appendChild(Object.assign(document.createElement('div'),{textContent:m, style:'padding:6px;font-weight:600'})));
    barangays.forEach(b=>{
      grid.appendChild(Object.assign(document.createElement('div'),{textContent:b, style:'padding:6px;font-weight:600'}));
      months.forEach(m=>{
        const rows = data.filter(r=>r.barangay===b && r.date.slice(0,7)===m);
        const vol = rows.reduce((s,r)=>s+r.volume,0);
        const comp = rows.length ? Math.round(rows.reduce((s,r)=>s+r.compliance,0)/rows.length) : null;
        const cell = document.createElement('div'); cell.style.padding='6px'; cell.style.textAlign='center';
        if(!rows.length){ cell.textContent='-'; cell.style.color='#999'; }
        else{ cell.textContent = vol+'kg / '+comp+'%'; cell.style.background = `rgba(135,170,80,${Math.max(0.12, (100-comp)/100)})`; }
        grid.appendChild(cell);
      });
    });
    container.appendChild(grid);
  }

  // events
  ['filter-from','filter-to','filter-district','filter-report'].forEach(id=>{ const el = document.getElementById(id); if(el) el.addEventListener('change', renderCharts); });

  const pdfBtn = document.getElementById('generate-pdf');
  if(pdfBtn) pdfBtn.addEventListener('click', function(){
    const printWin = window.open('', '', 'width=900,height=800');
    printWin.document.write('<html><head><title>Report</title></head><body>');
    printWin.document.write('<h2>Waste Report</h2>');
    const heat = document.getElementById('heatmap');
    printWin.document.write('<div>'+(heat?heat.innerHTML:'')+'</div>');
    printWin.document.close(); printWin.focus(); setTimeout(()=>printWin.print(),500);
  });

  const excelBtn = document.getElementById('generate-excel');
  if(excelBtn) excelBtn.addEventListener('click', function(){
    const filtered = getFiltered();
    if(typeof XLSX !== 'undefined'){
      const aoa = [['Date','District','Barangay','Type','Volume','Compliance']];
      filtered.forEach(r=> aoa.push([r.date,r.district,r.barangay,r.type,r.volume,r.compliance]));
      const ws = XLSX.utils.aoa_to_sheet(aoa); const wb = XLSX.utils.book_new(); XLSX.utils.book_append_sheet(wb, ws, 'Report'); XLSX.writeFile(wb, 'waste-report.xlsx');
    } else {
      // fallback CSV
      const header = ['Date','District','Barangay','Type','Volume','Compliance'];
      const rows = filtered.map(r=> [r.date,r.district,r.barangay,r.type,r.volume,r.compliance].map(c=> String(c).includes(',')?`"${String(c).replace(/"/g,'""')}"`:c).join(','));
      const csv = [header.join(','), ...rows].join('\n'); const blob = new Blob([csv], {type:'text/csv'}); const url = URL.createObjectURL(blob); const a = document.createElement('a'); a.href=url; a.download='waste-report.csv'; document.body.appendChild(a); a.click(); a.remove(); URL.revokeObjectURL(url);
    }
  });

  // Modal logic: open when clicking a chart canvas to pop it out
  const modal = document.getElementById('chart-modal');
  const modalImg = document.getElementById('chart-modal-img');
  const modalTitle = document.getElementById('chart-modal-title');
  const modalClose = document.getElementById('chart-modal-close');
  const chartDownload = document.getElementById('chart-download');
  const chartPrint = document.getElementById('chart-print');

  function openChartModal(canvas, title){
    try{
      const dataUrl = canvas.toDataURL('image/png');
      modalImg.src = dataUrl;
      modalTitle.textContent = title || 'Chart';
      modal.style.display = 'flex';
      // download handler
      chartDownload.onclick = function(){
        const a = document.createElement('a'); a.href = dataUrl; a.download = (title||'chart') + '.png'; document.body.appendChild(a); a.click(); a.remove();
      };
      chartPrint.onclick = function(){
        const w = window.open('','_blank'); w.document.write('<html><head><title>'+ (title||'Chart') +'</title></head><body style="margin:0"><img src="'+dataUrl+'" style="max-width:100%;height:auto"></body></html>'); w.document.close(); w.focus(); setTimeout(()=>w.print(),300);
      };
    }catch(e){ alert('Unable to open chart image.'); }
  }

  // Attach click listeners to canvases
  if(pieEl){ pieEl.style.cursor = 'pointer'; pieEl.addEventListener('click', ()=> openChartModal(pieEl, 'Waste Composition')); }
  if(barEl){ barEl.style.cursor = 'pointer'; barEl.addEventListener('click', ()=> openChartModal(barEl, 'Compliance per Barangay')); }

  if(modalClose) modalClose.addEventListener('click', ()=> modal.style.display='none');
  if(modal) modal.addEventListener('click', (ev)=>{ if(ev.target===modal) modal.style.display='none'; });

  // initial render wrapped so any errors get surfaced to the status banner
  try{
    renderCharts();
    setStatus('Ready — rendered demo dataset ('+demo.length+' rows).');
  }catch(err){
    setStatus('Render error: '+(err && err.message?err.message:err), true);
    console.error(err);
  }

})();
