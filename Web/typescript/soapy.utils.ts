export function findByProperty(arr: any[], property: string, value: any) {
  for (var item of arr) {
    if (item[property] === value) {
      return item;
    }
  }

  return null;
}

