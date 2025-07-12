// const $ = require("jquery")

$(document).ready(() => {
  console.log("Read page loaded")
  loadReadBooks()
})

function loadReadBooks() {
  console.log("Loading read books...")

  $.ajax({
    url: "get_books.php?status=read",
    method: "GET",
    dataType: "json",
    success: (response) => {
      console.log("Read books loaded:", response)
      if (response.success) {
        displayBooks(response.books, "read")
      } else {
        if (response.redirect) {
          // ログインが必要な場合
          $(".result").html(`
            <div class="text-center p-8">
              <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6 max-w-md mx-auto">
                <h3 class="text-lg font-medium text-yellow-800 mb-2">ログインが必要です</h3>
                <p class="text-yellow-700 mb-4">読了済みリストを表示するにはログインしてください。</p>
                <a href="${response.redirect}" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded transition-colors">
                  ログインページへ
                </a>
              </div>
            </div>
          `)
        } else {
          $(".result").html('<div class="text-center p-4 text-red-600">データの読み込みに失敗しました。</div>')
        }
      }
    },
    error: (xhr, status, error) => {
      console.error("Load error:", error)
      $(".result").html('<div class="text-center p-4 text-red-600">データの読み込み中にエラーが発生しました。</div>')
    },
  })
}

function displayBooks(books, type) {
  console.log("Displaying books:", books)

  if (!books || books.length === 0) {
    const message = type === "read" ? "読了済みの本がありません。" : "積読の本がありません。"
    $(".result").html(`<div class="text-center p-4 text-gray-600">${message}</div>`)
    return
  }

  const title = type === "read" ? "読了済み" : "積読"
  let html = `<div class="container mx-auto p-4"><h2 class="text-lg font-bold mb-4">${title} (${books.length}件)</h2>`

  books.forEach((book) => {
    const title = book.title || "タイトル不明"
    const authors = book.authors || "著者不明"
    const description = book.description || "説明なし"
    const comment = book.comment || ""
    const imageUrl = book.image_url || "https://via.placeholder.com/128x192?text=No+Image"
    const savedDate = new Date(book.created_at).toLocaleDateString("ja-JP")

    html += `
            <div class="mb-6 p-4 border border-gray-200 rounded-lg bg-white" data-book-id="${book.id}">
                <div class="flex gap-4 h-auto min-h-48">
                    <!-- 書籍画像 -->
                    <div class="flex-shrink-0">
                        <img src="${imageUrl}" alt="${title}" class="w-32 h-48 object-cover rounded shadow-sm">
                    </div>
                    <!-- 書籍情報 -->
                    <div class="flex-grow flex flex-col">
                        <div class="flex justify-between items-start mb-2">
                            <h3 class="text-lg font-semibold text-gray-900">${title}</h3>
                            <button class="delete-btn text-red-500 hover:text-red-700 p-1" 
                                    data-book-id="${book.id}" 
                                    data-book-title="${title}"
                                    title="削除">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                            </button>
                        </div>
                        <p class="text-gray-700 mb-2"><strong>著者:</strong> ${authors}</p>
                        <p class="text-gray-500 mb-2 text-sm"><strong>登録日:</strong> ${savedDate}</p>
                        
                        <!-- 説明文 -->
                        <div class="mb-3">
                            <h4 class="text-sm font-medium text-gray-700 mb-1">説明:</h4>
                            <div class="max-h-24 overflow-y-auto p-2 bg-gray-50 rounded">
                                <p class="text-gray-600 text-sm leading-relaxed">${description}</p>
                            </div>
                        </div>
                        
                        <!-- コメント -->
                        <div class="mb-3">
                            <div class="flex justify-between items-center mb-1">
                                <h4 class="text-sm font-medium text-gray-700">コメント:</h4>
                                <button class="edit-comment-btn text-blue-500 hover:text-blue-700 text-sm" 
                                        data-book-id="${book.id}" 
                                        data-current-comment="${comment}"
                                        title="コメントを編集">
                                    <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                    </svg>
                                    ${comment ? "編集" : "追加"}
                                </button>
                            </div>
                            <div class="comment-display" data-book-id="${book.id}">
                                ${
                                  comment
                                    ? `<div class="p-3 bg-blue-50 rounded border-l-4 border-blue-400">
                                        <p class="text-gray-700 text-sm leading-relaxed">${comment}</p>
                                    </div>`
                                    : `<div class="p-3 bg-gray-50 rounded border border-dashed border-gray-300">
                                        <p class="text-gray-500 text-sm italic">コメントがありません</p>
                                    </div>`
                                }
                            </div>
                        </div>
                        
                        <!-- ステータス表示 -->
                        <div class="flex gap-2 mt-auto">
                            <span class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm font-medium">
                                読了済み
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        `
  })

  html += "</div>"
  $(".result").html(html)

  // イベントを設定
  setupDeleteEvents()
  setupCommentEditEvents()
}

function setupDeleteEvents() {
  $(".delete-btn").on("click", function () {
    const bookId = $(this).data("book-id")
    const bookTitle = $(this).data("book-title")

    if (confirm(`「${bookTitle}」を削除しますか？\nこの操作は取り消せません。`)) {
      deleteBook(bookId, $(this).closest("[data-book-id]"))
    }
  })
}

function setupCommentEditEvents() {
  $(".edit-comment-btn").on("click", function () {
    const bookId = $(this).data("book-id")
    const currentComment = $(this).data("current-comment") || ""

    showCommentEditModal(bookId, currentComment)
  })
}

function showCommentEditModal(bookId, currentComment) {
  $("#modal-title").text("コメントを編集")
  $("#book-comment").val(currentComment)
  $("#comment-modal").removeClass("hidden")

  // モーダルのイベントハンドラーを一度削除してから再設定
  $("#cancel-comment, #save-comment").off("click")

  // キャンセルボタン
  $("#cancel-comment").on("click", () => {
    $("#comment-modal").addClass("hidden")
  })

  // 保存ボタン
  $("#save-comment").on("click", () => {
    const newComment = $("#book-comment").val()
    $("#comment-modal").addClass("hidden")
    updateComment(bookId, newComment)
  })

  // モーダル外クリックで閉じる
  $("#comment-modal").on("click", function (e) {
    if (e.target === this) {
      $(this).addClass("hidden")
    }
  })
}

function updateComment(bookId, comment) {
  console.log("Updating comment for book ID:", bookId, "Comment:", comment)

  $.ajax({
    url: "update_comment.php",
    method: "POST",
    contentType: "application/json",
    data: JSON.stringify({ id: bookId, comment: comment }),
    success: (response) => {
      console.log("Update success:", response)

      if (response.success) {
        // コメント表示を更新
        const commentDisplay = $(`.comment-display[data-book-id="${bookId}"]`)
        const editButton = $(`.edit-comment-btn[data-book-id="${bookId}"]`)

        if (comment.trim()) {
          commentDisplay.html(`
                        <div class="p-3 bg-blue-50 rounded border-l-4 border-blue-400">
                            <p class="text-gray-700 text-sm leading-relaxed">${comment}</p>
                        </div>
                    `)
          editButton.html(`
                        <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                        編集
                    `)
        } else {
          commentDisplay.html(`
                        <div class="p-3 bg-gray-50 rounded border border-dashed border-gray-300">
                            <p class="text-gray-500 text-sm italic">コメントがありません</p>
                        </div>
                    `)
          editButton.html(`
                        <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                        追加
                    `)
        }

        // data属性も更新
        editButton.data("current-comment", comment)

        alert("コメントを更新しました！")
      } else {
        alert("コメントの更新に失敗しました: " + (response.error || "不明なエラー"))
      }
    },
    error: (xhr, status, error) => {
      console.error("Update error:", error)
      alert("コメントの更新中にエラーが発生しました")
    },
  })
}

function deleteBook(bookId, bookElement) {
  console.log("Deleting book ID:", bookId)

  $.ajax({
    url: "delete_book.php",
    method: "POST",
    contentType: "application/json",
    data: JSON.stringify({ id: bookId }),
    success: (response) => {
      console.log("Delete success:", response)

      if (response.success) {
        // 削除されたアイテムをフェードアウト
        bookElement.fadeOut(300, function () {
          $(this).remove()

          // リストが空になった場合の処理
          if ($(".result [data-book-id]").length === 0) {
            $(".result").html('<div class="text-center p-4 text-gray-600">読了済みの本がありません。</div>')
          } else {
            // 件数を更新
            const remainingCount = $(".result [data-book-id]").length
            $(".result h2").text(`読了済み (${remainingCount}件)`)
          }
        })

        alert(`「${response.deleted_record.title}」を削除しました。`)
      } else {
        alert("削除に失敗しました: " + (response.error || "不明なエラー"))
      }
    },
    error: (xhr, status, error) => {
      console.error("Delete error:", error)
      alert("削除中にエラーが発生しました")
    },
  })
}
