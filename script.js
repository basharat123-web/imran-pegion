document.addEventListener("DOMContentLoaded", function () {
  const pigeonData = [
      {
          id: 1,
          name: "استاد ڈاکٹر ملک عدنان جمیل جمیل",
          pigeon1: "18:25:02",
          pigeon2: "18:28:32",
          pigeon3: "18:40:05",
          pigeon4: "18:53:30",
          pigeon5: "18:58:20",
          pigeon6: "18:58:21",
          pigeon7: "19:01:10",
          total: "89:25:00"
      },
      {
          id: 2,
          name: "استاد پیر فواد عظیم امیر کلان امرہ کلان",
          pigeon1: "14:41:36",
          pigeon2: "17:47:55",
          pigeon3: "18:53:35",
          pigeon4: "18:57:31",
          pigeon5: "18:58:33",
          pigeon6: "19:15:49",
          pigeon7: "19:20:48",
          total: "85:55:47"
      },
      {
          id: 3,
          name: "سجاد مبخی سبیاکو ث",
          pigeon1: "15:07:35",
          pigeon2: "15:57:00",
          pigeon3: "18:52:20",
          pigeon4: "18:53:40",
          pigeon5: "18:56:10",
          pigeon6: "19:23:17",
          pigeon7: "19:24:02",
          total: "84:34:04"
      },
      {
          id: 4,
          name: "استاد ملک زبیر اعوان کہریاں",
          pigeon1: "14:43:48",
          pigeon2: "16:32:58",
          pigeon3: "18:37:28",
          pigeon4: "18:28:51",
          pigeon5: "18:39:17",
          pigeon6: "18:46:49",
          pigeon7: "18:49:51",
          total: "82:39:02"
      },
      {
          id: 5,
          name: "استاد ڈاکٹر حسین الدزداقی سبیاکو",
          pigeon1: "14:43:09",
          pigeon2: "16:52:29",
          pigeon3: "17:03:03",
          pigeon4: "17:07:09",
          pigeon5: "18:46:55",
          pigeon6: "19:23:52",
          pigeon7: "19:41:41",
          total: "81:38:18"
      },
      {
        id: 6,
        name: "استاد ڈاکٹر حسین الدزداقی سبیاکو",
        pigeon1: "14:43:09",
        pigeon2: "16:52:29",
        pigeon3: "17:03:03",
        pigeon4: "17:07:09",
        pigeon5: "18:46:55",
        pigeon6: "19:23:52",
        pigeon7: "19:41:41",
        total: "81:38:18"
    },
    {
        id: 7,
        name: "استاد ڈاکٹر حسین الدزداقی سبیاکو",
        pigeon1: "20:43:09",
        pigeon2: "20:52:29",
        pigeon3: "17:03:03",
        pigeon4: "17:07:09",
        pigeon5: "18:46:55",
        pigeon6: "19:23:52",
        pigeon7: "19:41:41",
        total: "90:38:18"
    }
  ];

  
  if (typeof Swiper !== "undefined") {
    var swiper = new Swiper(".mySwiper", {
        loop: true, // Infinite loop
        autoplay: {
            delay: 3000, // Auto-slide every 3 seconds
            disableOnInteraction: false, // Keep autoplay after manual swipe
        },
        pagination: {
            el: ".swiper-pagination",
            clickable: true, // Click on dots to navigate
        },
        navigation: {
            nextEl: ".swiper-button-next",
            prevEl: ".swiper-button-prev",
        },
        effect: "slide", // Default slide effect
    });
} else {
    console.error("Swiper is not loaded. Check your Swiper library inclusion.");
}

  function populateTable() {
    const tableBody = document.querySelector("#pigeonTable tbody");
    tableBody.innerHTML = ""; // Clear the table before inserting sorted data

    let totalPigeons = 0;
    let totalFlown = 0;

    // Sort the data based on the largest time value
    pigeonData.sort((a, b) => {
        let maxA = Math.max(
            ...[a.pigeon1, a.pigeon2, a.pigeon3, a.pigeon4, a.pigeon5, a.pigeon6, a.pigeon7]
                .map(time => time ? new Date("1970-01-01T" + time) : 0)
        );
        let maxB = Math.max(
            ...[b.pigeon1, b.pigeon2, b.pigeon3, b.pigeon4, b.pigeon5, b.pigeon6, b.pigeon7]
                .map(time => time ? new Date("1970-01-01T" + time) : 0)
        );
        return maxB - maxA; // Sort in descending order (latest time first)
    });

    pigeonData.forEach((data) => {
        let pigeonTimes = [
            data.pigeon1, data.pigeon2, data.pigeon3, 
            data.pigeon4, data.pigeon5, data.pigeon6, data.pigeon7
        ];

        let flownCount = pigeonTimes.filter(time => time !== "").length;
        totalPigeons += pigeonTimes.length;
        totalFlown += flownCount;

        let row = `
            <tr>
                <td>${data.id}</td>
                <td>${data.name}</td>
                <td>${data.pigeon1}</td>
                <td>${data.pigeon2}</td>
                <td>${data.pigeon3}</td>
                <td>${data.pigeon4}</td>
                <td>${data.pigeon5}</td>
                <td>${data.pigeon6}</td>
                <td>${data.pigeon7}</td>
                <td>${data.total}</td>
            </tr>
        `;
        tableBody.innerHTML += row;
    });

    let remainingPigeons = totalPigeons - totalFlown;
    document.getElementById("totalPigeons").innerText = totalPigeons;
    document.getElementById("totalFlown").innerText = totalFlown;
    document.getElementById("remainingPigeons").innerText = remainingPigeons;
}

populateTable();

  function updateSummary() {
    fetch("summary_data.php") // Call PHP script
        .then(response => response.json())
        .then(data => {
            document.getElementById("totalPigeons").innerText = data.totalPigeons;
            document.getElementById("totalFlown").innerText = data.flownPigeons;
            document.getElementById("remainingPigeons").innerText = data.remainingPigeons;
        })
        .catch(error => console.error("Error fetching data:", error));
}

// Call function on page load
updateSummary();

// Auto-refresh every 5 seconds (optional)
setInterval(updateSummary, 5000);
// Get all editable cells
const cells = document.querySelectorAll('td[contenteditable="true"]');

// Add event listener to each cell
cells.forEach(cell => {
    cell.addEventListener('blur', function() {
        // Remove blink class from all cells to avoid multiple blinking
        cells.forEach(c => c.classList.remove('blink'));

        // Add blink class to the edited cell
        this.classList.add('blink');
    });
});

});
