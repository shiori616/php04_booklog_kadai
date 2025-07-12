// Import jQuery
// const $ = require("jquery")

// DOMが読み込まれた後に実行
$(document).ready(() => {
  console.log("jQuery loaded and ready") // デバッグ用

  // フォーム送信時の処理（Enterキーでも動作）
  $("#search-form").on("submit", (e) => {
    e.preventDefault() // フォームのデフォルト送信を防ぐ
    console.log("Form submitted") // デバッグ用
    performSearch()
  })

  // 検索ボタンがクリックされたときの処理
  $("#search-button").on("click", (e) => {
    e.preventDefault() // デフォルト動作を防ぐ
    console.log("Search button clicked") // デバッグ用
    performSearch()
  })

  function performSearch() {
    console.log("performSearch function called") // デバッグ用

    // 前に表示されていた項目を削除
    $(".result").empty()

    // 入力されたキーワードを取得
    const keyword = $("#keyword").val().trim()
    console.log("Keyword:", keyword) // デバッグ用

    // キーワードが空でない場合のみ処理を実行
    if (keyword === "") {
      alert("キーワードを入力してください")
      return
    }

    // 検索中表示
    $(".result").html('<div class="text-center p-4"><p class="text-gray-600">検索中...</p></div>')

    // Google Books APIを使用して書籍情報を取得
    const url = "https://www.googleapis.com/books/v1/volumes?q=" + encodeURIComponent(keyword) + "&maxResults=10"
    console.log("API URL:", url) // デバッグ用

    $.ajax({
      url: url,
      dataType: "json",
      success: (data) => {
        console.log("API Success:", data) // デバッグ用
        displayResults(data)
      },
      error: (xhr, status, error) => {
        console.error("API Error:", error) // デバッグ用
        $(".result").html('<div class="text-center p-4 text-red-600">検索中にエラーが発生しました。</div>')
      },
    })
  }

  function displayResults(data) {
    console.log("displayResults called with:", data) // デバッグ用

    if (!data.items || data.items.length === 0) {
      $(".result").html('<div class="text-center p-4 text-gray-600">検索結果が見つかりませんでした。</div>')
      return
    }

    let html = `<div class="container mx-auto p-4"><h2 class="text-lg font-bold mb-4">検索結果 (${Math.min(data.items.length, 10)}件表示)</h2>`

    // 検索結果を最大10件まで表示
    const maxResults = 10
    const itemsToShow = data.items.slice(0, maxResults)

    itemsToShow.forEach((item, index) => {
      const book = item.volumeInfo
      const title = book.title || "タイトル不明"
      const authors = book.authors ? book.authors.join(", ") : "著者不明"
      const description = book.description || "説明なし"

      // industryIdentifiersを取得
      const industryIdentifiers = book.industryIdentifiers || []
      console.log("Industry Identifiers:", industryIdentifiers) // デバッグ用

      // 画像のURLを取得（thumbnailを使用）
      const imageUrl =
        book.imageLinks && book.imageLinks.thumbnail
          ? book.imageLinks.thumbnail.replace("http://", "https://")
          : "https://via.placeholder.com/128x192?text=No+Image"

      html += `
                <div class="mb-6 p-4 border border-gray-200 rounded-lg bg-white">
                    <div class="flex gap-4 h-48">
                        <!-- 書籍画像 -->
                        <div class="flex-shrink-0">
                            <img src="${imageUrl}" alt="${title}" class="w-32 h-48 object-cover rounded shadow-sm">
                        </div>
                        <!-- 書籍情報 -->
                        <div class="flex-grow flex flex-col h-48">
                            <h3 class="text-lg font-semibold text-gray-900 mb-2 flex-shrink-0">${title}</h3>
                            <p class="text-gray-700 mb-2 flex-shrink-0"><strong>著者:</strong> ${authors}</p>
                            <!-- スクロール可能な説明文エリア -->
                            <div class="flex-grow overflow-y-auto p-3">
                                <p class="text-gray-600 text-sm leading-relaxed">${description}</p>
                            </div>
                            <!-- ボタンエリア -->
                            <div class="flex gap-2 mt-2 flex-shrink-0">
                                <button class="read-btn bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded text-sm font-medium transition-colors" 
                                        data-title="${title}" 
                                        data-authors="${authors}" 
                                        data-image="${imageUrl}"
                                        data-description="${description}"
                                        data-industry-identifiers='${JSON.stringify(industryIdentifiers)}'>
                                    読んだ
                                </button>
                                <button class="tsundoku-btn bg-orange-500 hover:bg-orange-600 text-white px-4 py-2 rounded text-sm font-medium transition-colors" 
                                        data-title="${title}" 
                                        data-authors="${authors}" 
                                        data-image="${imageUrl}"
                                        data-description="${description}"
                                        data-industry-identifiers='${JSON.stringify(industryIdentifiers)}'>
                                    積読
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `
    })

    html += "</div>"
    $(".result").html(html)

    // ボタンのクリックイベントを設定
    setupButtonEvents()
  }

  function setupButtonEvents() {
    // 「読んだ」ボタンのクリックイベント
    $(".read-btn").on("click", function () {
      const industryIdentifiers = $(this).data("industry-identifiers") || []
      const clickDateTime = new Date().toISOString()

      const bookData = {
        title: $(this).data("title"),
        authors: $(this).data("authors"),
        imageUrl: $(this).data("image"),
        description: $(this).data("description"),
        industryIdentifiers: industryIdentifiers,
        buttonType: "read",
        clickDateTime: clickDateTime,
        status: "read",
      }

      showCommentModal(bookData, $(this), "読了済み")
    })

    // 「積読」ボタンのクリックイベント
    $(".tsundoku-btn").on("click", function () {
      const industryIdentifiers = $(this).data("industry-identifiers") || []
      const clickDateTime = new Date().toISOString()

      const bookData = {
        title: $(this).data("title"),
        authors: $(this).data("authors"),
        imageUrl: $(this).data("image"),
        description: $(this).data("description"),
        industryIdentifiers: industryIdentifiers,
        buttonType: "tsundoku",
        clickDateTime: clickDateTime,
        status: "tsundoku",
      }

      showCommentModal(bookData, $(this), "積読")
    })
  }

  // コメントモーダル表示関数
  function showCommentModal(bookData, buttonElement, actionType) {
    $("#modal-title").text(`${actionType}リストに追加 - コメント入力`)
    $("#book-comment").val("")
    $("#comment-modal").removeClass("hidden")

    // モーダルのイベントハンドラーを一度削除してから再設定
    $("#cancel-comment, #save-comment").off("click")

    // キャンセルボタン
    $("#cancel-comment").on("click", () => {
      $("#comment-modal").addClass("hidden")
    })

    // 保存ボタン
    $("#save-comment").on("click", () => {
      const comment = $("#book-comment").val().trim()
      bookData.comment = comment
      console.log("Comment added to bookData:", comment) // デバッグ用
      $("#comment-modal").addClass("hidden")
      saveBookToDB(bookData, buttonElement)
    })

    // モーダル外クリックで閉じる
    $("#comment-modal").on("click", function (e) {
      if (e.target === this) {
        $(this).addClass("hidden")
      }
    })
  }

  function saveBookToDB(bookData, buttonElement) {
    console.log("Saving book to DB:", bookData)

    // ボタンを無効化
    buttonElement.prop("disabled", true).text("保存中...")

    $.ajax({
      url: "insert.php",
      method: "POST",
      contentType: "application/json",
      data: JSON.stringify(bookData),
      success: (response) => {
        console.log("Save success:", response)

        try {
          const result = typeof response === "string" ? JSON.parse(response) : response

          if (result.success) {
            // ボタンの状態を変更
            if (bookData.buttonType === "read") {
              buttonElement.removeClass("bg-green-500 hover:bg-green-600").addClass("bg-green-700")
              buttonElement.text("読了済み")
            } else {
              buttonElement.removeClass("bg-orange-500 hover:bg-orange-600").addClass("bg-orange-700")
              buttonElement.text("積読済み")
            }

            const commentText = bookData.comment ? `\nコメント: ${bookData.comment}` : ""
            alert(
              `「${bookData.title}」を${bookData.buttonType === "read" ? "読了済み" : "積読リスト"}に保存しました！${commentText}`,
            )
          } else {
            // エラーの場合
            if (result.redirect) {
              // ログインが必要な場合
              if (confirm(result.error + "\nログインページに移動しますか？")) {
                window.location.href = result.redirect
              }
            } else {
              alert("保存に失敗しました: " + (result.error || "不明なエラー"))
            }
            buttonElement.prop("disabled", false).text(bookData.buttonType === "read" ? "読んだ" : "積読")
          }
        } catch (e) {
          console.error("Response parsing error:", e)
          alert("レスポンスの解析に失敗しました")
          buttonElement.prop("disabled", false).text(bookData.buttonType === "read" ? "読んだ" : "積読")
        }
      },
      error: (xhr, status, error) => {
        console.error("Save error:", error)
        alert("保存中にエラーが発生しました")
        buttonElement.prop("disabled", false).text(bookData.buttonType === "read" ? "読んだ" : "積読")
      },
    })
  }
})
