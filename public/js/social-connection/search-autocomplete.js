$(document).ready(function(){
    let searchTimeout = null; // Biến dùng để delay (Debounce)

    $("#smart-search").keyup(function(){
        clearTimeout(searchTimeout); // Hủy lệnh tìm kiếm cũ nếu đang gõ liên tục
        let keyword = $(this).val();

        if(keyword.length >= 1) {
            // Thiết lập delay 250ms theo đúng yêu cầu
            searchTimeout = setTimeout(function() {
                $.ajax({
                    type: "GET",
                    dataType: "json",
                    url: "/social/search/autocomplete", // Gọi lên route web.php
                    data: {"keyword": keyword},
                    success: function(response){
                        let htmlStr = "";
                        
                        if(response.length === 0) {
                            htmlStr = "<div class='p-2 text-muted'>Không tìm thấy ai...</div>";
                        } else {
                            response.forEach(function(item){
                                // Hiển thị kết quả (Bạn có thể CSS lại cho đẹp sau)
                                htmlStr += `
                                <div class="search-item p-2 border-bottom d-flex align-items-center">
                                    <div class="mr-2">👤</div>
                                    <div>
                                        <strong>${item.name}</strong><br>
                                        <small class="text-muted">${item.subtitle}</small>
                                    </div>
                                    <button class="btn btn-sm btn-primary ml-auto btn-add-friend" data-id="${item.id}">Kết bạn</button>
                                </div>`;
                            });
                        }
                        $("#search-result-div").html(htmlStr).show();
                    }
                });
            }, 250); // Mốc 250ms chuẩn
        } else {
            $("#search-result-div").html("").hide();
        }
    });

    // Ẩn bảng kết quả khi click ra ngoài
    $(document).click(function(e) {
        if (!$(e.target).closest('#smart-search, #search-result-div').length) {
            $('#search-result-div').hide();
        }
    });
});