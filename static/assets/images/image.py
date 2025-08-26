from pathlib import Path

WorkDIR = Path(__file__).resolve().parent
output_file = 'images.txt'
img_prefix= 'https://cdn.jsdelivr.net/gh/lopinv/BuchMistrz@main/static/assets/images/'

try:
    # 先将图片文件收集到列表中
    image_list = []
    # 遍历当前目录下的所有文件和文件夹
    for item in WorkDIR.iterdir():
        # 检查是否为文件，如果是，则添加到列表
        if item.is_file() and item.suffix in ['.jpg', '.png', '.jpeg', '.gif', '.bmp', '.webp', '.avif']:
            image_url = f'"{img_prefix}{item.name}",\n'
            # 检查是否已存在，避免重复
            if image_url not in image_list:
                image_list.append(image_url)
    
    # 将列表内容写入文件
    with open(output_file, 'w', encoding='utf-8') as f:
        f.writelines(image_list)
    
    print(f"文件名已成功写入 '{output_file}'。")

except Exception as e:
    print(f"发生错误：{e}")
