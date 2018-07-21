function courseenrolfilter(course,filterpage){
    $("#id_idnumber, #id_empnumber").select2({
        ajax: {
            url: M.cfg.wwwroot+"/local/costcenter/filterajax.php",
            dataType: 'json',
            delay: 250,
            data: function (params) {
                return {
                    courseid: course,
                    action: 'courseenroll',
                    filterpage : filterpage,
                    type: 'idnumber',
                    q: params.term, //search term
                    page: params.page
                };
            },
            processResults: function (data, params) {
                params.page = params.page || 1;
        
                return {
                    results: data.items,
                    pagination: {
                        more: (params.page * 30) < data.total_count
                    }
                };
            },
            cache: true
        },
        escapeMarkup: function (markup) { return markup; }, // let our custom formatter work
        minimumInputLength: 1,
        templateResult: formatRepo, // omitted for brevity, see the source of this page
        templateSelection: formatRepoSelection // omitted for brevity, see the source of this page
    });
    
    $("#id_email").select2({
        ajax: {
            url: M.cfg.wwwroot+"/local/costcenter/filterajax.php",
            dataType: 'json',
            delay: 250,
            data: function (params) {
                return {
                    courseid: course,
                    filterpage : filterpage,
                    action: 'courseenroll',
                    type: 'email',
                    q: params.term, // search term
                    page: params.page
                };
            },
            processResults: function (data, params) {
                params.page = params.page || 1;
        
                return {
                    results: data.items,
                    pagination: {
                        more: (params.page * 50) < data.total_count
                    }
                };
            },
            cache: true
        },
        escapeMarkup: function (markup) { return markup; }, // let our custom formatter work
        minimumInputLength: 1,
        templateResult: formatRepo, // omitted for brevity, see the source of this page
        templateSelection: formatRepoSelection // omitted for brevity, see the source of this page
    });
    
    $("#id_band").select2({
        ajax: {
            url: M.cfg.wwwroot+"/local/costcenter/filterajax.php",
            dataType: 'json',
            delay: 250,
            data: function (params) {
                return {
                    courseid: course,
                    filterpage : filterpage,
                    action: 'courseenroll',
                    type: 'band',
                    q: params.term, // search term
                    page: params.page
                };
            },
            processResults: function (data, params) {
                params.page = params.page || 1;
        
                return {
                    results: data.items,
                    pagination: {
                        more: (params.page * 50) < data.total_count
                    }
                };
            },
            cache: true
        },
        escapeMarkup: function (markup) { return markup; }, // let our custom formatter work
        minimumInputLength: 1,
        templateResult: formatRepo, // omitted for brevity, see the source of this page
        templateSelection: formatRepoSelection // omitted for brevity, see the source of this page
    });
    
    $("#id_department").select2({
        ajax: {
            url: M.cfg.wwwroot+"/local/costcenter/filterajax.php",
            dataType: 'json',
            delay: 250,
            data: function (params) {
                return {
                    courseid: course,
                    filterpage : filterpage,
                    action: 'courseenroll',
                    type: 'department',
                    q: params.term, // search term
                    page: params.page
                };
            },
            processResults: function (data, params) {
                params.page = params.page || 1;
        
                return {
                    results: data.items,
                    pagination: {
                        more: (params.page * 50) < data.total_count
                    }
                };
            },
            cache: true
        },
        escapeMarkup: function (markup) { return markup; }, // let our custom formatter work
        minimumInputLength: 1,
        templateResult: formatRepo, // omitted for brevity, see the source of this page
        templateSelection: formatRepoSelection // omitted for brevity, see the source of this page
    });
    
    $("#id_subdepartment").select2({
        ajax: {
            url: M.cfg.wwwroot+"/local/costcenter/filterajax.php",
            dataType: 'json',
            delay: 250,
            data: function (params) {
                return {
                    courseid: course,
                    filterpage : filterpage,
                    action: 'courseenroll',
                    type: 'subdepartment',
                    q: params.term, // search term
                    page: params.page
                };
            },
            processResults: function (data, params) {
                params.page = params.page || 1;
        
                return {
                    results: data.items,
                    pagination: {
                        more: (params.page * 50) < data.total_count
                    }
                };
            },
            cache: true
        },
        escapeMarkup: function (markup) { return markup; }, // let our custom formatter work
        minimumInputLength: 1,
        templateResult: formatRepo, // omitted for brevity, see the source of this page
        templateSelection: formatRepoSelection // omitted for brevity, see the source of this page
    });
    
    $("#id_sub_sub_department").select2({
        ajax: {
            url: M.cfg.wwwroot+"/local/costcenter/filterajax.php",
            dataType: 'json',
            delay: 250,
            data: function (params) {
                return {
                    courseid: course,
                    filterpage : filterpage,
                    action: 'courseenroll',
                    type: 'sub_sub_department',
                    q: params.term, // search term
                    page: params.page
                };
            },
            processResults: function (data, params) {
                params.page = params.page || 1;
        
                return {
                    results: data.items,
                    pagination: {
                        more: (params.page * 50) < data.total_count
                    }
                };
            },
            cache: true
        },
        escapeMarkup: function (markup) { return markup; }, // let our custom formatter work
        minimumInputLength: 1,
        templateResult: formatRepo, // omitted for brevity, see the source of this page
        templateSelection: formatRepoSelection // omitted for brevity, see the source of this page
    });
    
    $("#id_designation").select2({
        ajax: {
            url: M.cfg.wwwroot+"/local/costcenter/filterajax.php",
            dataType: 'json',
            delay: 250,
            data: function (params) {
                return {
                    courseid: course,
                    filterpage : filterpage,
                    action: 'courseenroll',
                    type: 'designation',
                    q: params.term, // search term
                    page: params.page
                };
            },
            processResults: function (data, params) {
                params.page = params.page || 1;
        
                return {
                    results: data.items,
                    pagination: {
                        more: (params.page * 50) < data.total_count
                    }
                };
            },
            cache: true
        },
        escapeMarkup: function (markup) { return markup; }, // let our custom formatter work
        minimumInputLength: 1,
        templateResult: formatRepo, // omitted for brevity, see the source of this page
        templateSelection: formatRepoSelection // omitted for brevity, see the source of this page
    });
    
    $("#id_costcenter, #id_organization").select2({
        ajax: {
            url: M.cfg.wwwroot+"/local/costcenter/filterajax.php",
            dataType: 'json',
            delay: 250,
            data: function (params) {
                return {
                    courseid: course,
                    filterpage : filterpage,
                    action: 'courseenroll',
                    type: 'costcenter',
                    q: params.term, // search term
                    page: params.page
                };
            },
            processResults: function (data, params) {
                params.page = params.page || 1;
        
                return {
                    results: data.items,
                    pagination: {
                        more: (params.page * 50) < data.total_count
                    }
                };
            },
            cache: true
        },
        escapeMarkup: function (markup) { return markup; }, // let our custom formatter work
        minimumInputLength: 1,
        templateResult: formatRepo, // omitted for brevity, see the source of this page
        templateSelection: formatRepoSelection // omitted for brevity, see the source of this page
    });
    
    $("#id_lpassignusers").select2({
        ajax: {
            url: M.cfg.wwwroot+"/local/costcenter/filterajax.php",
            dataType: 'json',
            delay: 250,
            data: function (params) {
                return {
                    courseid: course,
                    filterpage : filterpage,
                    action: 'courseenroll',
                    type: 'empname',
                    q: params.term, // search term
                    page: params.page
                };
            },
            processResults: function (data, params) {
                params.page = params.page || 1;
        
                return {
                    results: data.items,
                    pagination: {
                        more: (params.page * 50) < data.total_count
                    }
                };
            },
            cache: true
        },
        escapeMarkup: function (markup) { return markup; }, // let our custom formatter work
        minimumInputLength: 1,
        templateResult: formatRepo, // omitted for brevity, see the source of this page
        templateSelection: formatRepoSelection // omitted for brevity, see the source of this page
    });
}

function formatRepo (repo) {
    if (repo.loading) return repo.text;
    var markup = "<div class='select2-result-repository clearfix'>" +
          "<div class='select2-result-repository__title'>" + repo.filtername + "</div>";
    return markup;
}

function formatRepoSelection (repo) {
    return repo.filtername || repo.text;
}