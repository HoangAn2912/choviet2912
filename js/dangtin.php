<script>
document.addEventListener('DOMContentLoaded', function () {
  const dangTinBtn = document.querySelector('.btn-dang-tin');
  const modal = document.getElementById('dangTinModal');
  const danhMucChaList = document.getElementById('danh-muc-cha-list');
  const formDangTin = document.getElementById('form-dang-tin'); // Form mới
  const modalSubtitle = document.getElementById('modal-subtitle');
  const backBtn = document.getElementById('backBtn');
  const closeBtn = document.getElementById('closeBtn');

  let danhMucGocHtml = '';
  let currentLevel = 'cha'; // cha | con | form

  if (dangTinBtn) {
    dangTinBtn.addEventListener('click', function () {
      modal.style.display = 'block';
    });
  }

  if (closeBtn) {
    closeBtn.addEventListener('click', function () {
      modal.style.display = 'none';
    });
  }

  window.onclick = function (event) {
    if (event.target == modal) {
      modal.style.display = "none";
    }
  }

  if (danhMucChaList) {
    danhMucGocHtml = danhMucChaList.innerHTML;

    function attachDanhMucChaClick() {
    danhMucChaList.querySelectorAll('li').forEach(li => {
        li.onclick = function (e) {
            const id = li.getAttribute('data-id'); // lấy ID của dòng đang click

            if (currentLevel === 'cha') {
                // Nếu đang ở cha, load danh mục con
                fetch('controller/cCategory.php?action=getSubcategories&id_cha=' + id)
                    .then(response => response.json())
                    .then(data => {
                        if (Array.isArray(data) && data.length > 0) {
                            currentLevel = 'con';
                            backBtn.style.display = 'inline-block';
                            modalSubtitle.innerText = 'Chọn danh mục';

                            let html = '';
                            data.forEach(sub => {
                                html += `
                                    <li class="list-group-item list-group-item-action d-flex justify-content-between align-items-center" data-id="${sub.id}" style="cursor: pointer;">
                                        <div class="d-flex align-items-center" style="gap: 10px;">
                                            <i class="fas fa-folder" style="color: #3D464D;"></i>
                                            <span style="font-weight: 500; color: #333;">${sub.category_name}</span>
                                        </div>
                                        <i class="fas fa-chevron-right" style="color: #888;"></i>
                                    </li>
                                `;
                            });
                            danhMucChaList.innerHTML = html;
                            attachDanhMucChaClick(); // Gán lại sự kiện cho danh mục con
                        } else {
                            alert('Không có danh mục con.');
                        }
                    })
                    .catch(err => {
                        console.error('Lỗi load danh mục con:', err);
                        alert('Đã có lỗi xảy ra khi tải danh mục.');
                    });

            } else if (currentLevel === 'con') {
                // Nếu đang ở danh mục con --> chọn ID sản phẩm con!
                const idLoaiSanPhamInput = document.getElementById('idLoaiSanPham');
                idLoaiSanPhamInput.value = id; // <-- Gán ID loại sản phẩm được chọn

                // Show form đăng tin
                currentLevel = 'form';
                backBtn.style.display = 'inline-block';
                danhMucChaList.style.display = 'none';
                formDangTin.style.display = 'block';
                modalSubtitle.innerText = 'Đăng tin';
            }
        };
    });
}


    attachDanhMucChaClick();

    if (backBtn) {
      backBtn.addEventListener('click', function () {
        if (currentLevel === 'form') {
          // Từ form -> quay lại danh mục con
          formDangTin.style.display = 'none';
          danhMucChaList.style.display = 'block';
          modalSubtitle.innerText = 'Chọn danh mục';
          currentLevel = 'con';
        } else if (currentLevel === 'con') {
          // Từ danh mục con -> quay lại danh mục cha
          currentLevel = 'cha';
          backBtn.style.display = 'none';
          modalSubtitle.innerText = 'Chọn danh mục';
          danhMucChaList.innerHTML = danhMucGocHtml;
          danhMucChaList.style.display = 'block';
          formDangTin.style.display = 'none';
          attachDanhMucChaClick();
        }
      });
    }
  }
});
</script>

<script>
document.addEventListener('DOMContentLoaded', function () {
  const form = document.getElementById('submitForm');
  const hinhAnhInput = document.getElementById('hinhAnh');

  form.addEventListener('submit', function (e) {
  // e.preventDefault(); <-- bỏ dòng này

  // Validate...
  const tieuDe = document.getElementById('tieuDe').value.trim();
  if (!tieuDe) {
    alert('Vui lòng nhập tiêu đề bài đăng.');
    return false; // <-- Dùng return false
  }

  const giaBan = document.getElementById('giaBan').value.trim();
  if (!giaBan || isNaN(giaBan) || Number(giaBan) <= 0) {
    alert('Vui lòng nhập giá bán hợp lệ.');
    return false;
  }

  const moTa = document.getElementById('moTa').value.trim();
  if (!moTa) {
    alert('Vui lòng nhập mô tả chi tiết.');
    return false;
  }

  const files = hinhAnhInput.files;
  if (files.length < 2 || files.length > 6) {
    alert('Vui lòng chọn từ 2 đến 6 hình ảnh.');
    return false;
  }
  for (let i = 0; i < files.length; i++) {
    const fileType = files[i].type;
    if (fileType !== 'image/jpeg' && fileType !== 'image/png' && fileType !== 'image/jpg') {
      alert('Chỉ chấp nhận hình ảnh .jpg, .jpeg, .png.');
      return false;
    }
  }

  // Nếu mọi thứ đều hợp lệ, form sẽ tự submit
});

});


</script>

