// Robust DataTables bootstrap with guaranteed visible search + optional year filtering.
(function() {
  var domLayout = "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>>" +
                  "<'row'<'col-sm-12'tr>>" +
                  "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>";

  function ensureDataTables(callback) {
    if (window.jQuery && $.fn && $.fn.DataTable) {
      callback();
      return;
    }
    // Inject CDN CSS/JS as a fallback.
    var head = document.getElementsByTagName('head')[0];
    var cssId = 'dt-cdn-css';
    if (!document.getElementById(cssId)) {
      var link = document.createElement('link');
      link.id = cssId;
      link.rel = 'stylesheet';
      link.href = 'https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css';
      head.appendChild(link);
    }
    var jsId = 'dt-cdn-js';
    if (!document.getElementById(jsId)) {
      var script = document.createElement('script');
      script.id = jsId;
      script.src = 'https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js';
      script.onload = function() {
        var bsScript = document.createElement('script');
        bsScript.src = 'https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js';
        bsScript.onload = callback;
        head.appendChild(bsScript);
      };
      head.appendChild(script);
    }
  }

  function buildControlsAndBind($table, dtInstance) {
    if ($table.data('dt-controls-added')) { return; }
    $table.data('dt-controls-added', true);

    var container = $table.closest('.table-responsive');
    var host = container.length ? container[0] : $table.get(0);

    var row = document.createElement('div');
    row.className = 'row mb-3 dt-extra-controls';

    var searchCol = document.createElement('div');
    searchCol.className = 'col-sm-12 col-md-5';
    var label = document.createElement('label');
    label.className = 'd-block';
    label.textContent = 'Recherche rapide';
    var input = document.createElement('input');
    input.type = 'search';
    input.className = 'form-control';
    input.placeholder = 'Rechercher...';
    label.appendChild(input);
    searchCol.appendChild(label);
    row.appendChild(searchCol);

    var yearColumnAttr = $table.attr('data-year-column');
    var yearSelect = null;
    if (typeof yearColumnAttr !== 'undefined') {
      yearSelect = document.createElement('select');
      yearSelect.className = 'form-control';
      var defaultOpt = document.createElement('option');
      defaultOpt.value = '';
      defaultOpt.textContent = 'Toutes les années';
      yearSelect.appendChild(defaultOpt);

      var yearColIdx = parseInt(yearColumnAttr, 10) || 0;
      var years = {};
      $table.find('tbody tr').each(function() {
        var tr = this;
        var attr = tr.getAttribute('data-year-values') || '';
        if (attr) {
          attr.split(/\s+/).forEach(function(y){ if(/^\d{4}$/.test(y)) years[y]=true; });
        } else {
          var cells = tr.cells || [];
          if (cells[yearColIdx]) {
            var txt = (cells[yearColIdx].innerText || '').trim();
            var maybe = txt.substr(0,4);
            if (/^\d{4}$/.test(maybe)) { years[maybe]=true; }
          }
        }
      });
      Object.keys(years).sort().reverse().forEach(function(y){
        var opt = document.createElement('option');
        opt.value = y;
        opt.textContent = y;
        yearSelect.appendChild(opt);
      });

      var yearCol = document.createElement('div');
      yearCol.className = 'col-sm-6 col-md-3';
      var yearLabel = document.createElement('label');
      yearLabel.className = 'd-block';
      yearLabel.textContent = 'Filtrer par année';
      yearLabel.appendChild(yearSelect);
      yearCol.appendChild(yearLabel);
      row.appendChild(yearCol);
    }

    var btnCol = document.createElement('div');
    btnCol.className = 'col-sm-6 col-md-2 d-flex align-items-end';
    var btn = document.createElement('button');
    btn.type = 'button';
    btn.className = 'btn btn-secondary w-100';
    btn.textContent = 'Filtrer';
    btnCol.appendChild(btn);
    row.appendChild(btnCol);

    if (host && host.parentNode) {
      host.parentNode.insertBefore(row, host);
    } else {
      $table.before(row);
    }

    var runFilter = function() {
      var term = (input.value || '').toLowerCase();
      // Normalize common separators so "31-2024" matches "31/2024"
      term = term.replace(/-/g, '/');
      var yearVal = yearSelect ? yearSelect.value : '';

      // Apply via DataTables if present
      if (dtInstance && typeof dtInstance.search === 'function') {
        dtInstance.search(term || '');
        // Do not constrain by column here; let the manual fallback below handle year filtering consistently.
        dtInstance.draw();
      }

      // Manual fallback to guarantee filtering even if DataTables fails to initialize
      var rows = $table.find('tbody tr');
      rows.each(function() {
        var text = (this.getAttribute('data-search-text') || this.innerText || '').toLowerCase().replace(/-/g,'/'); // normalize
        var matchesTerm = term ? (text.indexOf(term) !== -1) : true;
        var matchesYear = true;
        if (yearVal) {
          var attr = this.getAttribute('data-year-values') || '';
          var yearText = (attr || text).replace(/-/g,'/');
          matchesYear = yearText.indexOf(yearVal) !== -1;
        }
        this.style.display = (matchesTerm && matchesYear) ? '' : 'none';
      });
    };

    input.addEventListener('input', runFilter);
    if (yearSelect) yearSelect.addEventListener('change', runFilter);
    btn.addEventListener('click', runFilter);

    // Expose controls on the table element for other scripts if needed
    $table.data('dt-search-input', input);
    if (yearSelect) $table.data('dt-year-select', yearSelect);
  }

  function bindYearFilter($table, dtInstance) {
    var yearFilterSelector = $table.attr('data-year-filter');
    var yearColumnAttr = $table.attr('data-year-column');
    var yearColumnIndex = (typeof yearColumnAttr !== 'undefined') ? parseInt(yearColumnAttr, 10) : NaN;
    if (typeof yearFilterSelector !== 'string') { return; }
    var $filter = $(yearFilterSelector);
    if (!$filter.length) { return; }

    if (dtInstance && $.fn.dataTable && $.fn.dataTable.ext && $.fn.dataTable.ext.search) {
      var filterFn = function(settings, data, dataIndex) {
        if (settings.nTable !== $table[0]) { return true; }
        var yearVal = $filter.val();
        if (!yearVal) { return true; }
        var compare = '';
        if (settings.aoData && settings.aoData[dataIndex] && settings.aoData[dataIndex].nTr) {
          compare = settings.aoData[dataIndex].nTr.getAttribute('data-year-values') || '';
        }
        if (!compare && !Number.isNaN(yearColumnIndex)) {
          compare = (data[yearColumnIndex] || '').replace(/<[^>]+>/g, ' ');
        }
        if (!compare) { return false; }
        return compare.indexOf(yearVal) !== -1;
      };
      $.fn.dataTable.ext.search.push(filterFn);
      $table.on('destroy.dt', function() {
        var idx = $.fn.dataTable.ext.search.indexOf(filterFn);
        if (idx !== -1) {
          $.fn.dataTable.ext.search.splice(idx, 1);
        }
      });
      $filter.on('change keyup', function() {
        dtInstance.draw();
      });
    } else {
      // Manual fallback when DataTables isn't present
      var manualFilter = function() {
        var yearVal = $filter.val() || '';
        var rows = $table.find('tbody tr');
        rows.each(function() {
          var tr = this;
          var attr = tr.getAttribute('data-year-values') || '';
          var text = attr || tr.innerText || '';
          if (!yearVal) {
            tr.style.display = '';
          } else {
            tr.style.display = (text.indexOf(yearVal) !== -1) ? '' : 'none';
          }
        });
      };
      $filter.on('change keyup', manualFilter);
      manualFilter();
    }
  }

  function initTables() {
    $('table#dataTable').each(function() {
      var $table = $(this);
      var addExtras = $table.hasClass('dt-extra-controls');
      var options = {};
      if (addExtras) {
        options.dom = domLayout;
        // Keep searching enabled; we wire custom controls to the DataTables API.
        options.searching = true;
      }
      options.order = [];
      options.autoWidth = false;

      // Allow per-table disabling of ordering entirely.
      var orderingAttr = $table.attr('data-ordering');
      if (typeof orderingAttr !== 'undefined' && orderingAttr === 'false') {
        options.ordering = false;
      } else {
        var orderColAttr = $table.attr('data-order-column');
        if (typeof orderColAttr !== 'undefined') {
          var orderDir = $table.attr('data-order-direction') || 'desc';
          options.order = [[parseInt(orderColAttr, 10) || 0, orderDir]];
        }
      }
      var dt = null;
      if ($.fn.DataTable) {
        if ($.fn.DataTable.isDataTable($table)) {
          $table.DataTable().destroy();
        }
        dt = $table.DataTable(options);
      }
      if (addExtras) {
        buildControlsAndBind($table, dt);
        bindYearFilter($table, dt);
      }
    });
  }

  $(document).ready(function() {
    ensureDataTables(initTables);
    // Also attempt a delayed init in case CDN finishes later.
    setTimeout(function(){ if (window.jQuery) initTables(); }, 1500);
  });
})();
