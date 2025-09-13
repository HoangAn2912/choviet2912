// Theme Toggle Functionality
document.addEventListener("DOMContentLoaded", () => {
  const themeToggle = document.getElementById("themeToggle")
  const themeIcon = document.getElementById("themeIcon")
  const body = document.body

  // Check for saved theme preference or default to 'dark'
  const currentTheme = localStorage.getItem("theme") || "dark"

  console.log("[v0] Current theme on load:", currentTheme)

  // Apply the saved theme
  if (currentTheme === "light") {
    body.setAttribute("data-theme", "light")
    themeIcon.className = "fas fa-moon"
    console.log("[v0] Applied light theme")
  } else {
    body.removeAttribute("data-theme")
    themeIcon.className = "fas fa-sun"
    console.log("[v0] Applied dark theme")
  }

  // Theme toggle event listener
  if (themeToggle) {
    themeToggle.addEventListener("click", () => {
      console.log("[v0] Theme toggle clicked")
      const currentTheme = body.getAttribute("data-theme")
      console.log("[v0] Current theme before toggle:", currentTheme)

      if (currentTheme === "light") {
        // Switch to dark theme
        body.removeAttribute("data-theme")
        themeIcon.className = "fas fa-sun"
        localStorage.setItem("theme", "dark")
        console.log("[v0] Switched to dark theme")

        // Add animation effect
        themeToggle.style.transform = "rotate(360deg)"
        setTimeout(() => {
          themeToggle.style.transform = "rotate(0deg)"
        }, 300)
      } else {
        // Switch to light theme
        body.setAttribute("data-theme", "light")
        themeIcon.className = "fas fa-moon"
        localStorage.setItem("theme", "light")
        console.log("[v0] Switched to light theme")

        // Add animation effect
        themeToggle.style.transform = "rotate(-360deg)"
        setTimeout(() => {
          themeToggle.style.transform = "rotate(0deg)"
        }, 300)
      }
    })
  } else {
    console.log("[v0] Theme toggle button not found!")
  }

  // Add smooth transition effect on page load
  setTimeout(() => {
    body.style.transition = "all 0.3s ease"
  }, 100)
})

// Additional animations and interactions
document.addEventListener("DOMContentLoaded", () => {
  // Animate feature cards on scroll
  const observerOptions = {
    threshold: 0.1,
    rootMargin: "0px 0px -50px 0px",
  }

  const observer = new IntersectionObserver((entries) => {
    entries.forEach((entry) => {
      if (entry.isIntersecting) {
        entry.target.style.opacity = "1"
        entry.target.style.transform = "translateY(0)"
      }
    })
  }, observerOptions)

  // Observe feature cards
  const featureCards = document.querySelectorAll(".feature-card")
  featureCards.forEach((card) => {
    card.style.opacity = "0"
    card.style.transform = "translateY(30px)"
    card.style.transition = "all 0.6s ease"
    observer.observe(card)
  })

  // Add hover effects to carousel
  const carousel = document.getElementById("mainCarousel")
  if (carousel) {
    carousel.addEventListener("mouseenter", function () {
      this.style.transform = "scale(1.02)"
    })

    carousel.addEventListener("mouseleave", function () {
      this.style.transform = "scale(1)"
    })
  }
})
