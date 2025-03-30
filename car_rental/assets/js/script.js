'use strict';

/**
 * navbar toggle
 */

document.addEventListener('DOMContentLoaded', function () {
    const overlay = document.querySelector("[data-overlay]");
    const navbar = document.querySelector("[data-navbar]");
    const navToggleBtn = document.querySelector("[data-nav-toggle-btn]");
    const navbarLinks = document.querySelectorAll("[data-nav-link]");

    if (navToggleBtn) {
        const navToggleFunc = function () {
            navToggleBtn.classList.toggle("active");
            navbar.classList.toggle("active");
            overlay.classList.toggle("active");
        }

        navToggleBtn.addEventListener("click", navToggleFunc);

        for (let i = 0; i < navbarLinks.length; i++) {
            navbarLinks[i].addEventListener("click", navToggleFunc);
        }
    }

    /**
     * header active on scroll
     */

    const header = document.querySelector("[data-header]");
    if (header) {
        window.addEventListener("scroll", function() {
            window.scrollY >= 10 ? header.classList.add("active") : header.classList.remove("active");
        });
    }

    const searchInput = document.getElementById('searchInput');
    const suggestionsPanel = document.getElementById('recentSearches');

    if (!searchInput || !suggestionsPanel) {
        console.error("Some elements do not exist in the DOM.");
        return; // 요소가 없으면 함수 실행을 중지
    }

    function performSearch() {
        const searchQuery = searchInput.value;
        window.location.href = `search.php?search=${encodeURIComponent(searchQuery)}`;
    }

    searchInput.addEventListener('input', function() {
        const query = this.value;
        if (query.length > 1) {
            fetchSuggestions(query);
        } else {
            suggestionsPanel.style.display = 'none';
        }
    });

    searchInput.addEventListener('focus', function() {
        if (this.value.length === 0) {
            loadRecentSearches();
        } else {
            fetchSuggestions(this.value);
        }
    });

    searchInput.addEventListener('blur', function() {
        setTimeout(() => {
            suggestionsPanel.style.display = 'none';
        }, 300);
    });

    function fetchSuggestions(query) {
        const url = `search.php?search=${encodeURIComponent(query)}&ajax=1`;
        fetch(url)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                suggestionsPanel.innerHTML = ''; // 제안 패널 초기화
                suggestionsPanel.style.display = 'block';
                data.forEach(item => {
                    const div = document.createElement('div');
                    div.textContent = `${item.brand} - ${item.model} (${item.type})`;
                    div.onclick = () => {
                        searchInput.value = div.textContent;
                        performSearch();
                    };
                    suggestionsPanel.appendChild(div);
                });
            })
            .catch(error => {
                console.error('Error fetching suggestions:', error);
                suggestionsPanel.style.display = 'none'; // 오류 발생 시 제안 패널 숨기기
            });
    }

    window.addToCart = function(model, brand, pricePerDay, event) {
        event.preventDefault();  // 폼 제출의 기본 동작 방지

        fetch('reservation.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=add_to_cart&model=${encodeURIComponent(model)}&brand=${encodeURIComponent(brand)}&pricePerDay=${pricePerDay}`
        })
        .then(response => response.json())
        .then(data => {
            alert(data.message); // 성공 메시지 표시
            // 필요한 경우 페이지의 카트 표시나 다른 요소를 업데이트 할 수 있습니다
        })
        .catch(error => console.error('Error:', error));
    }
});

document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('searchInput');
    const suggestionsPanel = document.getElementById('suggestionsPanel');
    let timeoutId;
  
    searchInput.addEventListener('input', function() {
      clearTimeout(timeoutId);
      const query = this.value;
      timeoutId = setTimeout(() => {
        if (query.length > 0) {
          fetchSuggestions(query);
        } else {
          suggestionsPanel.innerHTML = '';
          suggestionsPanel.style.display = 'none';
        }
      }, 300); // 300ms의 디바운싱 시간
    });
  
    searchInput.addEventListener('focus', function() {
      if (this.value.length > 0) {
        suggestionsPanel.style.display = 'block';
      }
    });
  
    function fetchSuggestions(query) {
      const url = `search.php?search=${encodeURIComponent(query)}&ajax=1`;
      fetch(url)
          .then(response => response.json())
          .then(data => {
              suggestionsPanel.innerHTML = ''; // Reset the suggestions panel
              const suggestions = new Set(); // Use a Set to avoid duplicate entries
  
              data.forEach(item => {
                  // Add each type of suggestion to the Set
                  suggestions.add(item.brand);
                  suggestions.add(item.model);
                  suggestions.add(item.type);
              });
  
              suggestions.forEach(suggestion => {
                  const div = document.createElement('div');
                  div.textContent = suggestion;
                  div.classList.add('suggestion-item');
                  div.onclick = () => {
                      searchInput.value = suggestion;
                      performSearch();
                  };
                  suggestionsPanel.appendChild(div);
              });
  
              if (suggestions.size > 0) {
                  suggestionsPanel.style.display = 'block';
              } else {
                  suggestionsPanel.style.display = 'none';
              }
          })
          .catch(error => {
              console.error('Error fetching suggestions:', error);
              suggestionsPanel.style.display = 'none'; // Hide the panel if an error occurs
          });
  }
  });