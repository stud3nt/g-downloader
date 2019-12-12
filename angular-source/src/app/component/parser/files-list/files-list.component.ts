import { Component, Input, OnInit, Output} from '@angular/core';
import { ParserRequest } from "../../../model/parser-request";
import { NodeStatus } from "../../../enum/node-status";
import { ParsedFile } from "../../../model/parsed-file";
import { DownloaderService } from "../../../service/downloader.service";
import { FileStatus } from "../../../enum/file-status";
import { FileType } from "../../../enum/file-type";
import { ModalDataService } from "../../../service/data/modal-data.service";
import { ModalSize } from "../../../enum/modal-size";
import { ModalType } from "../../../enum/modal-type";

@Component({
  selector: 'app-files-list',
  templateUrl: './files-list.component.html'
})
export class FilesListComponent implements OnInit {

	@Input() parserRequest: ParserRequest;

	public objectStatus = NodeStatus;

	public FileStatus = FileStatus;
	public FileType = FileType;

	public lockTiles = false;

	constructor(
		protected downloaderService: DownloaderService,
		protected modal: ModalDataService
	) { }

	ngOnInit() {}

	public determineFileClass(file: ParsedFile) : string {
		let nodeClass = 'tile tile-250';

		if (file.hasStatus(FileStatus.Queued)) {
			nodeClass += ' queued';
		}

		if (file.hasStatus(FileStatus.Downloaded)) {
			nodeClass += ' downloaded';
		}

		return nodeClass;
	}

	/**
	 * Toggle file in queue (removes or adds);
	 *
	 * @param file
	 */
	public toggleFileQueue(file: ParsedFile) : void {
		if (this.lockTiles || file.hasStatus(FileStatus.Waiting)) {
			return;
		}

		file.addStatus(FileStatus.Waiting);

		this.downloaderService.toggleFileQueue(file).subscribe((result: ParsedFile) => {
			file.removeStatus(FileStatus.Waiting);

			if (result.hasStatus(FileStatus.Queued)) {
				file.addStatus(FileStatus.Queued);
			} else {
				file.removeStatus(FileStatus.Queued);
			}
		}, (error) => {
			file.removeStatus(FileStatus.Waiting);
		});
	}

	public openFilePreview(file: ParsedFile) : void {
		let modalTitle = file.title ? file.title : (file.name+'.'+file.extension);

		this.modal.open(ModalType.Preview, modalTitle).showLoader();

		this.downloaderService.toggleFilePreview(file).subscribe((result: ParsedFile) => {
			if (result.width < 600) {
				this.modal.setSize(ModalSize.Small);
			} else if (result.width > 600 && result.width < 1000) {
				this.modal.setSize(ModalSize.Medium);
			} else {
				this.modal.setSize(ModalSize.Large);
			}

			this.modal.setBody(result.htmlPreview).hideLoader();
		});
	}

}
