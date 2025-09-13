function showToast(message, type = "success") {
    const colors = {
      success: ["#FFD700", "#3D464D"],   // vàng + đen
      error: ["#FF5C5C", "#FFFFFF"],     // đỏ + trắng
      warning: ["#FFA500", "#000000"],   // cam + đen
    };
  
    const [bg, textColor] = colors[type] || ["#ccc", "#000"];
  
    Toastify({
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
    }).showToast();
  }
  