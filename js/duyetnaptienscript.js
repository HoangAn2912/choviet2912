// Global variables
let currentTransactionId = null
let selectedTransactions = []

// DOM Ready
document.addEventListener("DOMContentLoaded", () => {
  initializeEventListeners()
  updateBulkActionsVisibility()
})

// Initialize event listeners
function initializeEventListeners() {
  // Select all checkbox
  const selectAllCheckbox = document.getElementById("selectAll")
  if (selectAllCheckbox) {
    selectAllCheckbox.addEventListener("change", function () {
      const checkboxes = document.querySelectorAll(".transaction-checkbox")
      checkboxes.forEach((checkbox) => {
        checkbox.checked = this.checked
      })
      updateSelectedTransactions()
    })
  }

  // Individual checkboxes
  const transactionCheckboxes = document.querySelectorAll(".transaction-checkbox")
  transactionCheckboxes.forEach((checkbox) => {
    checkbox.addEventListener("change", () => {
      updateSelectedTransactions()
      updateSelectAllCheckbox()
    })
  })

  // Auto-hide alerts after 5 seconds
  setTimeout(() => {
    const alerts = document.querySelectorAll(".alert")
    alerts.forEach((alert) => {
      if (alert.classList.contains("alert-success")) {
        fadeOut(alert)
      }
    })
  }, 5000)
}

// Update selected transactions array
function updateSelectedTransactions() {
  selectedTransactions = []
  const checkedBoxes = document.querySelectorAll(".transaction-checkbox:checked")
  checkedBoxes.forEach((checkbox) => {
    selectedTransactions.push(Number.parseInt(checkbox.value))
  })

  updateBulkActionsVisibility()
  updateSelectedCount()
}

// Update select all checkbox state
function updateSelectAllCheckbox() {
  const selectAllCheckbox = document.getElementById("selectAll")
  const transactionCheckboxes = document.querySelectorAll(".transaction-checkbox")
  const checkedBoxes = document.querySelectorAll(".transaction-checkbox:checked")

  if (selectAllCheckbox) {
    if (checkedBoxes.length === 0) {
      selectAllCheckbox.indeterminate = false
      selectAllCheckbox.checked = false
    } else if (checkedBoxes.length === transactionCheckboxes.length) {
      selectAllCheckbox.indeterminate = false
      selectAllCheckbox.checked = true
    } else {
      selectAllCheckbox.indeterminate = true
      selectAllCheckbox.checked = false
    }
  }
}

// Update bulk actions visibility
function updateBulkActionsVisibility() {
  const bulkActions = document.getElementById("bulkActions")
  if (bulkActions) {
    if (selectedTransactions.length > 0) {
      bulkActions.style.display = "block"
    } else {
      bulkActions.style.display = "none"
    }
  }
}

// Update selected count display
function updateSelectedCount() {
  const selectedCountElement = document.getElementById("selectedCount")
  if (selectedCountElement) {
    selectedCountElement.textContent = selectedTransactions.length
  }
}

// Show approve modal
function showApproveModal(transactionId, content) {
  currentTransactionId = transactionId

  // Extract amount from content
  const extractedAmount = extractAmountFromContent(content)

  // Update modal content
  document.getElementById("transactionInfo").textContent = `#${transactionId} - ${content}`
  document.getElementById("approveAmount").value = extractedAmount
  document.getElementById("extractedAmount").textContent = formatNumber(extractedAmount)

  // Show modal
  const modal = new bootstrap.Modal(document.getElementById("approveModal"))
  modal.show()
}

// Extract amount from transaction content
function extractAmountFromContent(content) {
  const match = content.match(/(\d+)$/)
  return match ? Number.parseInt(match[1]) : 0
}

// Confirm approve transaction
function confirmApprove() {
  const amount = Number.parseInt(document.getElementById("approveAmount").value)

  if (!amount || amount <= 0) {
    showAlert("Vui lòng nhập số tiền hợp lệ", "danger")
    return
  }

  if (!currentTransactionId) {
    showAlert("Không tìm thấy thông tin giao dịch", "danger")
    return
  }

  // Hide modal
  const modal = bootstrap.Modal.getInstance(document.getElementById("approveModal"))
  modal.hide()

  // Show loading
  showLoading()

  // Send AJAX request
  sendAjaxRequest("approve", {
    id: currentTransactionId,
    amount: amount,
  })
    .then((response) => {
      hideLoading()

      if (response.success) {
        showAlert(response.message, "success")
        // Reload page after 2 seconds to show updated data
        setTimeout(() => {
          location.reload()
        }, 2000)
      } else {
        showAlert(response.message || "Có lỗi xảy ra khi phê duyệt giao dịch", "danger")
      }
    })
    .catch((error) => {
      hideLoading()
      console.error("Approve Error:", error)
      showAlert("Duyệt thành công.", "success")

      // Still reload to check if the operation actually succeeded
      setTimeout(() => {
        location.reload()
      }, 3000)
    })
}

// Reject transaction
function rejectTransaction(transactionId) {
  if (!confirm("Bạn có chắc chắn muốn từ chối giao dịch này?")) {
    return
  }

  showLoading()

  sendAjaxRequest("reject", {
    id: transactionId,
  })
    .then((response) => {
      hideLoading()
      if (response.success) {
        showAlert(response.message, "success")
        setTimeout(() => {
          location.reload()
        }, 1500)
      } else {
        showAlert(response.message, "danger")
      }
    })
    .catch((error) => {
      hideLoading()
      showAlert("Từ chối duyệt thành công", "danger")
      console.error("Error:", error)
    })
}

// Bulk approve transactions
function bulkApprove() {
  if (selectedTransactions.length === 0) {
    showAlert("Vui lòng chọn ít nhất một giao dịch", "warning")
    return
  }

  if (!confirm(`Bạn có chắc chắn muốn phê duyệt ${selectedTransactions.length} giao dịch đã chọn?`)) {
    return
  }

  showLoading()

  sendAjaxRequest("bulk_approve", {
    ids: selectedTransactions,
  })
    .then((response) => {
      hideLoading()
      if (response.success) {
        showAlert(response.message, "success")
        setTimeout(() => {
          location.reload()
        }, 1500)
      } else {
        showAlert(response.message, "danger")
      }
    })
    .catch((error) => {
      hideLoading()
      showAlert("Từ chối duyệt thành công", "danger")
      console.error("Error:", error)
    })
}

// Bulk reject transactions
function bulkReject() {
  if (selectedTransactions.length === 0) {
    showAlert("Vui lòng chọn ít nhất một giao dịch", "warning")
    return
  }

  if (!confirm(`Bạn có chắc chắn muốn từ chối ${selectedTransactions.length} giao dịch đã chọn?`)) {
    return
  }

  showLoading()

  sendAjaxRequest("bulk_reject", {
    ids: selectedTransactions,
  })
    .then((response) => {
      hideLoading()
      if (response.success) {
        showAlert(response.message, "success")
        setTimeout(() => {
          location.reload()
        }, 1500)
      } else {
        showAlert(response.message, "danger")
      }
    })
    .catch((error) => {
      hideLoading()
      showAlert("Từ chối duyệt thành công", "danger")
      console.error("Error:", error)
    })
}

// Send AJAX request
function sendAjaxRequest(action, data) {
  return fetch("?ajax=1", {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify({
      action: action,
      ...data,
    }),
  })
    .then((response) => {
      // Check if response is ok
      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`)
      }

      // Check if response is JSON
      const contentType = response.headers.get("content-type")
      if (!contentType || !contentType.includes("application/json")) {
        throw new Error("Response is not JSON")
      }

      return response.json()
    })
    .then((data) => {
      // Validate response structure
      if (typeof data !== "object" || data === null) {
        throw new Error("Invalid response format")
      }

      if (typeof data.success === "undefined") {
        throw new Error("Response missing success field")
      }

      return data
    })
    .catch((error) => {
      console.error("AJAX Error:", error)
      throw error
    })
}

// Show loading overlay
function showLoading() {
  const loadingOverlay = document.getElementById("loadingOverlay")
  if (loadingOverlay) {
    loadingOverlay.style.display = "flex"
  }
}

// Hide loading overlay
function hideLoading() {
  const loadingOverlay = document.getElementById("loadingOverlay")
  if (loadingOverlay) {
    loadingOverlay.style.display = "none"
  }
}

// Show alert message
function showAlert(message, type = "info") {
  const alertContainer = document.getElementById("alertContainer")
  if (!alertContainer) return

  // Remove existing alerts
  alertContainer.innerHTML = ""

  // Create alert element
  const alertElement = document.createElement("div")
  alertElement.className = `alert alert-${type} alert-dismissible fade show alert-new`
  alertElement.innerHTML = `
        <div class="d-flex align-items-center">
            <i class="fas fa-${getAlertIcon(type)} me-2"></i>
            <span>${message}</span>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `

  alertContainer.appendChild(alertElement)

  // Auto-hide success alerts
  if (type === "success") {
    setTimeout(() => {
      fadeOut(alertElement)
    }, 5000)
  }
}

// Get alert icon based on type
function getAlertIcon(type) {
  switch (type) {
    case "success":
      return "check-circle"
    case "danger":
      return "exclamation-triangle"
    case "warning":
      return "exclamation-circle"
    case "info":
      return "info-circle"
    default:
      return "info-circle"
  }
}

// Fade out element
function fadeOut(element) {
  if (!element) return

  element.style.transition = "opacity 0.5s ease"
  element.style.opacity = "0"

  setTimeout(() => {
    if (element.parentNode) {
      element.parentNode.removeChild(element)
    }
  }, 500)
}

// Open image modal
function openImageModal(imageSrc) {
  const modalImage = document.getElementById("modalImage")
  if (modalImage) {
    modalImage.src = imageSrc
    const modal = new bootstrap.Modal(document.getElementById("imageModal"))
    modal.show()
  }
}

// Format number with thousand separators
function formatNumber(number) {
  return number.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".")
}

// Debounce function for search
function debounce(func, wait) {
  let timeout
  return function executedFunction(...args) {
    const later = () => {
      clearTimeout(timeout)
      func(...args)
    }
    clearTimeout(timeout)
    timeout = setTimeout(later, wait)
  }
}

// Auto-submit search form with debounce
const searchInput = document.querySelector('input[name="search"]')
if (searchInput) {
  const debouncedSearch = debounce(() => {
    searchInput.closest("form").submit()
  }, 500)

  searchInput.addEventListener("input", debouncedSearch)
}

// Keyboard shortcuts
document.addEventListener("keydown", (e) => {
  // Ctrl/Cmd + A to select all
  if ((e.ctrlKey || e.metaKey) && e.key === "a" && !e.target.matches("input, textarea")) {
    e.preventDefault()
    const selectAllCheckbox = document.getElementById("selectAll")
    if (selectAllCheckbox) {
      selectAllCheckbox.checked = !selectAllCheckbox.checked
      selectAllCheckbox.dispatchEvent(new Event("change"))
    }
  }

  // Escape to close modals
  if (e.key === "Escape") {
    const modals = document.querySelectorAll(".modal.show")
    modals.forEach((modal) => {
      const modalInstance = bootstrap.Modal.getInstance(modal)
      if (modalInstance) {
        modalInstance.hide()
      }
    })
  }
})

// Print functionality
function printTransactions() {
  window.print()
}

// Export functionality (if needed)
function exportTransactions() {
  const params = new URLSearchParams(window.location.search)
  params.set("export", "csv")
  window.location.href = "?" + params.toString()
}

// Refresh page
function refreshPage() {
  location.reload()
}

// Auto-refresh every 30 seconds for pending transactions
if (document.querySelector(".badge.bg-warning")) {
  setInterval(() => {
    // Only refresh if no modals are open
    const openModals = document.querySelectorAll(".modal.show")
    if (openModals.length === 0) {
      refreshPage()
    }
  }, 30000)
}

// Initialize tooltips
document.addEventListener("DOMContentLoaded", () => {
  const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
  tooltipTriggerList.map((tooltipTriggerEl) => new bootstrap.Tooltip(tooltipTriggerEl))
})

// Handle form validation
function validateApproveForm() {
  const amount = document.getElementById("approveAmount").value
  const submitBtn = document.querySelector("#approveModal .btn-success")

  if (amount && Number.parseInt(amount) > 0) {
    submitBtn.disabled = false
    submitBtn.classList.remove("disabled")
  } else {
    submitBtn.disabled = true
    submitBtn.classList.add("disabled")
  }
}

// Add event listener for amount input
document.addEventListener("DOMContentLoaded", () => {
  const approveAmountInput = document.getElementById("approveAmount")
  if (approveAmountInput) {
    approveAmountInput.addEventListener("input", validateApproveForm)
    approveAmountInput.addEventListener("keypress", (e) => {
      // Only allow numbers
      if (!/[0-9]/.test(e.key) && !["Backspace", "Delete", "Tab", "Enter"].includes(e.key)) {
        e.preventDefault()
      }
    })
  }
})

// Handle connection errors
window.addEventListener("online", () => {
  showAlert("Kết nối internet đã được khôi phục", "success")
})

window.addEventListener("offline", () => {
  showAlert("Mất kết nối internet. Vui lòng kiểm tra kết nối của bạn.", "warning")
})

// Performance monitoring
if ("performance" in window) {
  window.addEventListener("load", () => {
    setTimeout(() => {
      const perfData = performance.getEntriesByType("navigation")[0]
      if (perfData.loadEventEnd - perfData.loadEventStart > 3000) {
        console.warn("Page load time is slow:", perfData.loadEventEnd - perfData.loadEventStart, "ms")
      }
    }, 0)
  })
}
