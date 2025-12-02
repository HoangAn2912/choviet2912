function showToast(message, type = "success") {
    const colors = {
      success: ["#FFD700", "#3D464D"],   // vàng + đen
      error: ["#FF5C5C", "#FFFFFF"],     // đỏ + trắng
      warning: ["#FFA500", "#000000"],   // cam + đen
    };
  
    const [bg, textColor] = colors[type] || ["#ccc", "#000"];
  
    const toast = Toastify({
      text: message,
      duration: 4000,
      close: true,
      gravity: "top",
      position: "right",
      style: {
        background: bg,
        color: textColor,
        fontWeight: "bold",
        borderRadius: "6px",
        padding: "10px 15px",
      },
      stopOnFocus: true,
    });
  
    toast.showToast();
  
    // Đổi màu dấu X (nút đóng) sang màu đen cho rõ hơn
    setTimeout(() => {
      try {
        const closeBtn = document.querySelector(".toastify .toast-close");
        if (closeBtn) {
          closeBtn.style.color = "#000000";
        }
      } catch (e) {
        console.warn("Không thể chỉnh màu nút đóng toast:", e);
      }
    }, 0);
  }
  