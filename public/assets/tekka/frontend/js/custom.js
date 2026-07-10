// navbar categorylist
const categoryList = document.querySelectorAll(".category-list li");
const categorySubList = document.querySelectorAll(".sub-category-wrapper");

categoryList.forEach((item) => {
    const firstSubItem = categorySubList[0];
    firstSubItem.classList.add("d-block");

    categorySubList.forEach((subItem) => {
        item.addEventListener("click", () => {
            const itemContent = item.getAttribute("data-filter");
            const itemClass = item.classList;
            const subItemContent = subItem.getAttribute("data-filter");
            categorySubList.forEach((subItem) => {
                if (itemContent == subItem.getAttribute("data-filter")) {
                    subItem.classList.add("d-block");
                } else {
                    subItem.classList.remove("d-block");
                }
            });
        });
    });
});

// window.onscroll = function () {
//     const header = document.querySelector("header.sticky-top");
//     const normalHeader = document.querySelector("header:not(.sticky-top)");
//     if (header && normalHeader) {
//         const headerHeight = normalHeader.offsetHeight;
//         const sticky = normalHeader.offsetTop || 0;
//         console.log("Sticky: ", window.scrollY, sticky, headerHeight);
//         if (window.scrollY > 150) {
//             header.classList.add("sticky-header");
//             header.style.display = "block";
//         } else {
//             header.classList.remove("sticky-header");
//             header.style.display = "none";
//         }
//     }
// };

const categoryWrappers = document.querySelectorAll(".category-wrapper");

categoryWrappers.forEach((wrapper) => {
    const categoryItems = wrapper.querySelectorAll(".category-item");

    categoryItems.forEach((categoryItem) => {
        categoryItem.addEventListener("click", () => {
            const isActive = categoryItem.classList.contains("active");

            if (isActive) {
                // Remove active class from clicked item and remove unactive class from all items
                categoryItem.classList.remove("active");

                categoryWrappers.forEach((innerWrapper) => {
                    const allCategoryItems =
                        innerWrapper.querySelectorAll(".category-item");
                    allCategoryItems.forEach((item) => {
                        item.classList.remove("unactive");
                    });
                });
            } else {
                // Add active class to clicked item and add unactive class to all other items
                categoryWrappers.forEach((innerWrapper) => {
                    const allCategoryItems =
                        innerWrapper.querySelectorAll(".category-item");
                    allCategoryItems.forEach((item) => {
                        item.classList.remove("active");
                        item.classList.add("unactive");
                    });
                });

                categoryItem.classList.remove("unactive");
                categoryItem.classList.add("active");
            }

            const subCategories = categoryItem.querySelector(
                ".subcategories-list"
            );
            const allSubCategories =
                document.querySelectorAll(".sub-categories");
            const showSubCategories = wrapper.querySelector(".sub-categories");

            if (subCategories && showSubCategories) {
                // Hide all subcategory containers
                allSubCategories.forEach((item) => {
                    item.classList.add("unactive");
                    item.classList.remove("active");
                });

                if (!isActive) {
                    // Show the current subcategory container if the clicked item was not active
                    showSubCategories.classList.remove("unactive");
                    showSubCategories.classList.add("active");
                    showSubCategories.innerHTML = "";

                    // Clone the subcategories list and append it
                    const clonedSubCategories = subCategories.cloneNode(true);
                    showSubCategories.appendChild(clonedSubCategories);
                }
            }
        });
    });
});

// category page end
// footer collaps start
$(".collaps-nav").on("click", function () {
    $(this).parent().toggleClass("collapsable-active");
});


jQuery(document).ready(function(){jQuery(".product-gallery").lightSlider({gallery:true,item:1,thumbItem:4,thumbMargin:10,});});

